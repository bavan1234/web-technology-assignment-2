package com.college.portal;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public final class DBUtil {
    private DBUtil() {}
    // Set environment variables DB_URL, DB_USER and DB_PASSWORD for your machine.
    public static Connection getConnection() throws SQLException {
        String url = System.getenv().getOrDefault(
            "DB_URL", "jdbc:mysql://localhost:3306/student_portal?useSSL=false&serverTimezone=UTC");
        String user = System.getenv().getOrDefault("DB_USER", "root");
        String password = System.getenv().getOrDefault("DB_PASSWORD", "");
        return DriverManager.getConnection(url, user, password);
    }
}
