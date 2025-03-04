package myPackage;

import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        boolean loggedIn = false;

        while (!loggedIn) {
            System.out.print("Enter Username: ");
            String username = scanner.nextLine();

            System.out.print("Enter Password: ");
            String password = scanner.nextLine();

            String role = AuthService.authenticateUser(username, password);

            if (role != null) {
                UserSession.startSession(username, role);
                System.out.println("Login successful! Welcome, " + username);
                loggedIn = true; 
                Dashboard.showDashboard(role, scanner);
            } else {
                System.out.println("Invalid username or password. Please try again.\n");
            }
        }

        scanner.close();
    }
}
