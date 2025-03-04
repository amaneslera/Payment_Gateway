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
                System.out.println("Feature not implemented yet.");
            }
        }
    }

    private static void displayMenu(String role) {
        switch (role) {
            case "Admin":
                System.out.println("1. Manage Users");
                System.out.println("2. View Reports");
                System.out.println("3. System Settings");
                System.out.println("4. Logout");
                break;
            case "Cashier":
                System.out.println("1. Process Payments");
                System.out.println("2. View Transactions");
                System.out.println("3. Logout");
                break;
            case "Manager":
                System.out.println("1. Monitor Sales");
                System.out.println("2. View Staff Performance");
                System.out.println("3. Generate Reports");
                System.out.println("4. Logout");
                break;
            default:
                System.out.println("Unknown Role. Contact administrator.");
        }
    }

    private static int getLogoutOption(String role) {
        return switch (role) {
            case "Admin", "Manager" ->
                4;
            case "Cashier" ->
                3;
            default ->
                -1;
        };
    }

}
