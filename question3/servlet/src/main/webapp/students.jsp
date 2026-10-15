<%@ page contentType="text/html;charset=UTF-8" %>
<%@ page import="java.util.*" %>
<%!
  // Escape special HTML characters before displaying database values.
  public String esc(Object value) {
    if (value == null) return "";
    return value.toString().replace("&", "&amp;").replace("<", "&lt;")
      .replace(">", "&gt;").replace("\"", "&quot;").replace("'", "&#x27;");
  }
%>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Student Profiles</title><link rel="stylesheet" href="style.css"></head><body>
<main class="container"><header><h1>Student Profiles</h1><a class="button" href="logout">Log out</a></header>
<% List<Map<String,Object>> students = (List<Map<String,Object>>)request.getAttribute("students");
   if (students == null || students.isEmpty()) { %><p>No student records found. Add records using the PHP admin page.</p><% }
   else { for (Map<String,Object> s : students) { %>
<section class="card"><h2><%= esc(s.get("name")) %></h2>
<p><strong>Roll number:</strong> <%= esc(s.get("roll")) %></p>
<p><strong>Email:</strong> <%= esc(s.get("email")) %></p><p><strong>Course:</strong> <%= esc(s.get("course")) %></p>
<% Object fileId = s.get("fileId"); Object fileName = s.get("fileName"); Object type = s.get("fileType");
   if (fileId != null && fileName != null) {
     String safeType = type == null ? "" : type.toString();
     if (safeType.startsWith("image/")) { %>
<img class="profile-image" src="file?id=<%= esc(fileId) %>" alt="Uploaded profile/document image">
<% } %><p>File: <a href="file?id=<%= esc(fileId) %>"><%= esc(fileName) %></a></p>
<% } else { %><p>No files uploaded for this student.</p><% } %>
</section>
<% } } %></main></body></html>
