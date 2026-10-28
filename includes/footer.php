<?php
/**
 * Public Layout Footer
 * Event Management System
 */
?>
<footer class="public-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <h3><i class="fas fa-calendar-check"></i> EventHub System</h3>
            <p style="color: #94a3b8; line-height: 1.6; font-size: 14px; margin-top: 10px;">
                A comprehensive, full-stack Event Management Web Application built with PHP, MySQL, HTML5, CSS3, and JavaScript. Supporting seamless event planning, artist assignments, sponsorship tracking, and resource management.
            </p>
        </div>

        <div class="footer-col">
            <h4>Quick Navigation</h4>
            <ul class="footer-links">
                <li><a href="<?= url('index.php') ?>">Home</a></li>
                <li><a href="<?= url('events.php') ?>">All Events</a></li>
                <li><a href="<?= url('login.php') ?>">Administrator Login</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>OEP Project Info</h4>
            <p style="color: #94a3b8; font-size: 13px; line-height: 1.6;">
                <strong>Course:</strong> Web Development OEP<br>
                <strong>Architecture:</strong> PHP + MySQL (PDO)<br>
                <strong>Security:</strong> Sessions, Bcrypt, CSRF, Prepared Statements.
            </p>
        </div>
    </div>

    <div class="footer-bottom">
        <div>&copy; <?= date('Y') ?> EventHub Management System. All Rights Reserved.</div>
        <div>Designed for Academic Open-Ended Problem (OEP) Submission</div>
    </div>
</footer>

<script src="<?= url('assets/js/script.js') ?>"></script>
</body>
</html>
