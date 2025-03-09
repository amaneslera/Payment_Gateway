package myPackage;

import java.sql.*;
import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;
import java.util.Scanner;

public class UserOperations {

    // Create Operation
    public static void createUser(String username, String email, String role, String password) {
        String sql = "INSERT INTO user (username, email, role, password_hash) VALUES (?, ?, ?, ?)";

        try (Connection conn = DatabaseUtil.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            String passwordHash = hashPassword(password);

            pstmt.setString(1, username);
            pstmt.setString(2, email);
            pstmt.setString(3, role);
            pstmt.setString(4, passwordHash);
            pstmt.executeUpdate();

            System.out.println("User created successfully.");

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Hash Password
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

    // Read Operation
    public static void readUsers() {
        String sql = "SELECT * FROM user";

        try (Connection conn = DatabaseUtil.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            System.out.println("User ID\tUsername\tEmail\tRole\tCreated At\tUpdated At");
            while (rs.next()) {
                System.out.println(rs.getInt("user_id") + "\t" +
                                   rs.getString("username") + "\t" +
                                   rs.getString("email") + "\t" +
                                   rs.getString("role") + "\t" +
                                   rs.getTimestamp("created_at") + "\t" +
                                   rs.getTimestamp("updated_at"));
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Update Operation
    public static void updateUser(int id, String username, String email, String role) {
        String sql = "UPDATE user SET username = ?, email = ?, role = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?";

        try (Connection conn = DatabaseUtil.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, username);
            pstmt.setString(2, email);
            pstmt.setString(3, role);
            pstmt.setInt(4, id);
            pstmt.executeUpdate();

            System.out.println("User updated successfully.");

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Delete Operation
    public static void deleteUser(int id) {
        String sql = "DELETE FROM user WHERE user_id = ?";

        try (Connection conn = DatabaseUtil.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setInt(1, id);
            pstmt.executeUpdate();

            System.out.println("User deleted successfully.");

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    // Main method for testing
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        boolean running = true;

        while (running) {
            System.out.println("\n=== User Operations ===");
            System.out.println("1. Create User");
            System.out.println("2. Read Users");
            System.out.println("3. Update User");
            System.out.println("4. Delete User");
            System.out.println("5. Exit");
            System.out.print("Enter your choice: ");
            int choice = scanner.nextInt();
            scanner.nextLine(); // Consume newline

            switch (choice) {
                case 1:
                    System.out.print("Enter Username: ");
                    String username = scanner.nextLine();
                    System.out.print("Enter Email: ");
                    String email = scanner.nextLine();
                    System.out.print("Enter Role: ");
                    String role = scanner.nextLine();
                    System.out.print("Enter Password: ");
                    String password = scanner.nextLine();
                    createUser(username, email, role, password);
                    break;
                case 2:
                    readUsers();
                    break;
                case 3:
                    System.out.print("Enter User ID to Update: ");
                    int idToUpdate = scanner.nextInt();
                    scanner.nextLine(); // Consume newline
                    System.out.print("Enter New Username: ");
                    String newUsername = scanner.nextLine();
                    System.out.print("Enter New Email: ");
                    String newEmail = scanner.nextLine();
                    System.out.print("Enter New Role: ");
                    String newRole = scanner.nextLine();
                    updateUser(idToUpdate, newUsername, newEmail, newRole);
                    break;
                case 4:
                    System.out.print("Enter User ID to Delete: ");
                    int idToDelete = scanner.nextInt();
                    System.out.print("Are you sure you want to delete this user? (yes/no): ");
                    String confirmation = scanner.next();
                    if ("yes".equalsIgnoreCase(confirmation)) {
                        deleteUser(idToDelete);
                    } else {
                        System.out.println("Delete operation cancelled.");
                    }
                    break;
                case 5:
                    running = false;
                    break;
                default:
                    System.out.println("Invalid choice. Please try again.");
            }
        }

        scanner.close();
    }
}
