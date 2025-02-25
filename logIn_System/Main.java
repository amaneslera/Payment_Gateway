package myPackage;

import java.sql.*;
import java.util.Scanner;

public class Main {

    static final String DB_URL = "jdbc:mysql://localhost:3306/pos_system";
    static final String DB_USER = "pos";
    static final String DB_PASS = "pos";

    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);

        System.out.print("Enter Username: ");
        String username = scanner.nextLine();

        System.out.print("Enter Password: ");
        String password = scanner.nextLine();

        if (authenticateUser(username, password)) {
            System.out.println("✅ Login successful! Welcome, " + username);
        } else {
            System.out.println("❌ Login failed! Invalid username or password.");
        }

        scanner.close();
    }

    // Method to authenticate user
    public static boolean authenticateUser(String username, String password) {
        boolean isAuthenticated = false;

        try {

            Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS);

            String sql = "SELECT * FROM Staff WHERE username = ? AND password_hash = ?";
            PreparedStatement pstmt = conn.prepareStatement(sql);
            pstmt.setString(1, username);
            pstmt.setString(2, password);

            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                isAuthenticated = true;
            }

            rs.close();
            pstmt.close();
            conn.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return isAuthenticated;
    }
}
