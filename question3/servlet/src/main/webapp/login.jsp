<%@ page contentType="text/html;charset=UTF-8" %>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Portal Login</title><link rel="stylesheet" href="style.css"></head>
<body><main class="card"><h1>Student Portal</h1><p>Sign in to view student profiles.</p>
<% if (request.getAttribute("error") != null) { %><p class="error"><%= request.getAttribute("error") %></p><% } %>
<form method="post" action="login"><label>Username<input name="username" required></label><label>Password<input type="password" name="password" required></label><button>Log in</button></form>
<p class="hint">Demo credentials: student / student123</p></main></body></html>
