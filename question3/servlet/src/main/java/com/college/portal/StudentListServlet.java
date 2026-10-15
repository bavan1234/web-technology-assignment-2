package com.college.portal;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.*;
import java.io.IOException;
import java.sql.*;
import java.util.*;

@WebServlet("/students")
public class StudentListServlet extends HttpServlet {
    @Override protected void doGet(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
        // Require an authenticated session before revealing student data.
        HttpSession session = request.getSession(false);
        if (session == null || session.getAttribute("username") == null) {
            response.sendRedirect(request.getContextPath() + "/login.jsp");
            return;
        }

        List<Map<String, Object>> students = new ArrayList<>();
        String sql = "SELECT s.id, s.name, s.roll_number, s.email, s.course, " +
            "f.id AS file_id, f.original_name, f.file_type " +
            "FROM students s LEFT JOIN student_files f ON f.student_id=s.id " +
            "ORDER BY s.name, f.upload_date DESC";
        try (Connection con = DBUtil.getConnection();
             PreparedStatement ps = con.prepareStatement(sql);
             ResultSet rs = ps.executeQuery()) {
            // Build a list for JSP rendering; LEFT JOIN keeps students without uploads.
            while (rs.next()) {
                Map<String, Object> row = new LinkedHashMap<>();
                row.put("id", rs.getInt("id"));
                row.put("name", rs.getString("name"));
                row.put("roll", rs.getString("roll_number"));
                row.put("email", rs.getString("email"));
                row.put("course", rs.getString("course"));
                row.put("fileId", (Integer) rs.getObject("file_id"));
                row.put("fileName", rs.getString("original_name"));
                row.put("fileType", rs.getString("file_type"));
                students.add(row);
            }
        } catch (SQLException e) {
            throw new ServletException("Could not retrieve student records. Check database settings.", e);
        }
        request.setAttribute("students", students);
        request.getRequestDispatcher("/students.jsp").forward(request, response);
    }
}
