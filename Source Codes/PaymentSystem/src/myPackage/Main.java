
package myPackage;

import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        boolean loggedIn = false;
        String role = null;

        while (!loggedIn) {
            System.out.print("Enter Username: ");
            String username = sc.nextLine();

            System.out.print("Enter Password: ");
            String password = sc.nextLine();

            role = AuthService.authenticateUser(username, password);

            if (role != null) {
                System.out.println("Login successful. Role: " + role);
                UserSession.startSession(username, role);
                loggedIn = true;
            } else {
                System.out.println("Invalid username or password. Please try again.");
            }
        }

        Dashboard.showDashboard(role, sc);

        sc.close();
    }
}
