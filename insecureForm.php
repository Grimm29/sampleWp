<?php
/**
 * Plugin Name: Insecure Form Test (SonarQube Test)
 * Description: Form sengaja tidak aman untuk keperluan test deteksi SonarQube. JANGAN dipakai di production.
 * Version: 1.0
 */

// 1. Tidak ada pengecekan hak akses (current_user_can) sebelum proses form
function insecure_form_handler() {

    // 2. Tidak ada verifikasi nonce -> rentan CSRF
    if ( isset( $_POST['submit_login'] ) ) {

        // 3. Input user diambil langsung tanpa sanitasi (sanitize_text_field dll)
        $username = $_POST['username'];
        $password = $_POST['password'];
        $search   = $_POST['search'];

        global $wpdb;

        // 4. SQL Injection: query dibangun pakai string concatenation, bukan prepared statement
        $result = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}users WHERE user_login = '" . $username . "' AND user_pass = '" . $password . "'"
        );

        // 5. Hardcoded credential/API key di source code
        $api_key = "sk_live_51H8xJ2example_hardcoded_secret_key";

        // 6. Output langsung ke halaman tanpa esc_html/esc_attr -> celah XSS stored/reflected
        echo "<div class='welcome'>Hasil pencarian untuk: " . $search . "</div>";

        // 7. eval() terhadap input yang tidak tepercaya
        if ( isset( $_POST['formula'] ) ) {
            eval( "\$calc_result = " . $_POST['formula'] . ";" );
            echo "Hasil: " . $calc_result;
        }

        // 8. File include berdasarkan input user -> Local File Inclusion (LFI)
        if ( isset( $_GET['page'] ) ) {
            include( $_GET['page'] . '.php' );
        }
    }

    ?>
    <!-- 9. Form dikirim tanpa nonce field, action ke URL http (bukan https) -->
    <form action="http://example.com/wp-admin/admin-post.php" method="GET">
        <input type="hidden" name="action" value="insecure_form_handler">

        <label>Username:</label>
        <input type="text" name="username"><br>

        <!-- 10. Password pakai type="text" + autocomplete aktif -->
        <label>Password:</label>
        <input type="text" name="password" autocomplete="on"><br>

        <label>Search:</label>
        <input type="text" name="search"><br>

        <label>Formula (contoh celah eval):</label>
        <input type="text" name="formula"><br>

        <input type="submit" name="submit_login" value="Submit">
    </form>
    <?php
}

// 11. Shortcode didaftarkan tanpa validasi/permission check tambahan
add_shortcode( 'insecure_test_form', 'insecure_form_handler' );

// 12. Endpoint admin-post juga didaftarkan tanpa nonce check di dalam handler-nya
add_action( 'admin_post_insecure_form_handler', 'insecure_form_handler' );
add_action( 'admin_post_nopriv_insecure_form_handler', 'insecure_form_handler' );
