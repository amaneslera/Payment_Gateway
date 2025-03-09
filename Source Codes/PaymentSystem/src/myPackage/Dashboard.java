package myPackage;

import java.util.Scanner;

public class Dashboard {

    public static void showDashboard(String role, Scanner scanner) {
        boolean isRunning = true;

        while (isRunning) {
            System.out.println("\n=== Dashboard ===");
            displayMenu(role);

            System.out.print("Enter your choice: ");
            int choice = scanner.nextInt();
            scanner.nextLine();

            if (choice == getLogoutOption(role)) {
                System.out.println("Logging out...");
                UserSession.endSession();
                isRunning = false;
            } else {
                handleChoice(choice, role, scanner);
            }
        }
    }

    private static void displayMenu(String role) {
        if ("admin".equalsIgnoreCase(role)) {
            System.out.println("1. Manage Users");
            System.out.println("2. View Sales");
            System.out.println("3. Inventory");
            System.out.println("0. Logout");
        } else {
            System.out.println("1. View Sales");
            System.out.println("2. Inventory");
            System.out.println("0. Logout");
        }
    }

    private static int getLogoutOption(String role) {
        return 0; // Define the logout option number
    }

    private static void handleChoice(int choice, String role, Scanner scanner) {
        switch (choice) {
            case 1:
                if ("admin".equalsIgnoreCase(role)) {
                    manageUsers(scanner);
                } else {
                    // Handle View Sales for non-admin users
                    System.out.println("View Sales selected.");
                }
                break;
            case 2:
                if ("admin".equalsIgnoreCase(role)) {
                    // Handle View Sales for admin users
                    System.out.println("View Sales selected.");
                } else {
                    // Handle Inventory for non-admin users
                    System.out.println("Inventory selected.");
                }
                break;
            case 3:
                if ("admin".equalsIgnoreCase(role)) {
                    // Handle Inventory for admin users
                    System.out.println("Inventory selected.");
                } else {
                    System.out.println("Invalid choice. Please try again.");
                }
                break;
            default:
                System.out.println("Invalid choice. Please try again.");
        }
    }

    private static void manageUsers(Scanner scanner) {
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
                    UserOperations.createUser(username, email, role, password);
                    break;
                case 2:
                    UserOperations.readUsers();
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
                    UserOperations.updateUser(idToUpdate, newUsername, newEmail, newRole);
                    break;
                case 4:
                    System.out.print("Enter User ID to Delete: ");
                    int idToDelete = scanner.nextInt();
                    System.out.print("Are you sure you want to delete this user? (yes/no): ");
                    String confirmation = scanner.next();
                    if ("yes".equalsIgnoreCase(confirmation)) {
                        UserOperations.deleteUser(idToDelete);
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
    }
}
