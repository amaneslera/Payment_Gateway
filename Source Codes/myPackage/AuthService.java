package myPackage;

import java.sql.*;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

public class AuthService {

    private static final String DB_URL = "jdbc:mysql://localhost:3306/pos_system";
    private static final String DB_USER = "pos";
    private static final String DB_PASS = "pos";

    public static String authenticateUser(String username, String password) {
        String role = null;

        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS); PreparedStatement pstmt = conn.prepareStatement("SELECT password_hash, role FROM user WHERE username = ?")) {

            pstmt.setString(1, username);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                String storedHash = rs.getString("password_hash");
                String hashedInput = hashPassword(password);

                if (storedHash.equals(hashedInput)) {
                    role = rs.getString("role");
                }
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return role;
    }

    public static String hashPassword(String password) {
        try {
            MessageDigest md = MessageDigest.getInstance("SHA-256");
            byte[] hashBytes = md.digest(password.getBytes());

            StringBuilder hexString = new StringBuilder();
            for (byte b : hashBytes) {
                hexString.append(String.format("%02x", b));
            }
            return hexString.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException("Error hashing password", e);
        }
    }
}
