package myPackage;

public class UserSession {

    private static String loggedInUser;
    private static String userRole;

    public static void startSession(String username, String role) {
        loggedInUser = username;
        userRole = role;
    }

    public static void endSession() {
        loggedInUser = null;
        userRole = null;
    }

    public static boolean isUserLoggedIn() {
        return loggedInUser != null;
    }

    public static String getUserRole() {
        return userRole;
    }
}
