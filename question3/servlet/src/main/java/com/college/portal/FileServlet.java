package com.college.portal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.*;
import java.nio.file.*;
import java.sql.*;

@WebServlet("/file")
public class FileServlet extends HttpServlet {
    @Override protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("username") == null) {
            response.sendError(HttpServletResponse.SC_UNAUTHORIZED);
            return;
        }
        int id;
        try { id = Integer.parseInt(request.getParameter("id")); }
        catch (Exception ex) { response.sendError(400, "Invalid file id"); return; }

        String storedName;
        String mime;
        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement(
                 "SELECT stored_name, file_type FROM student_files WHERE id=?")) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                if (!rs.next()) { response.sendError(404); return; }
                storedName = rs.getString("stored_name");
                mime = rs.getString("file_type");
            }
        } catch (SQLException e) {
            throw new ServletException("Could not find file metadata.", e);
        }

        // Files are stored outside the public web root; only DB-listed names can be streamed.
        String root = System.getenv().getOrDefault("STUDENT_UPLOAD_DIR", "uploads");
        Path path = Paths.get(root).resolve(Paths.get(storedName).getFileName().toString()).normalize();
        if (!Files.isRegularFile(path)) { response.sendError(404, "File not found on disk"); return; }
        response.setContentType(mime == null ? "application/octet-stream" : mime);
        response.setHeader("X-Content-Type-Options", "nosniff");
        response.setContentLengthLong(Files.size(path));
        try (InputStream in = Files.newInputStream(path); OutputStream out = response.getOutputStream()) {
            in.transferTo(out);
        }
    }
}
