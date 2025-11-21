<?php
define('TITLE', "Privacy Policy");
include '../assets/layouts/header.php';
check_verified();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<div class="container my-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title fw-bold">Privacy Policy</h3>
            <p class="card-text"><span class="fw-bold">Privacy Notice</span><br>120ms is committed to protecting your privacy. This Website Privacy Policy outlines the terms and conditions related to the handling of personal information that may be collected through this website.</p>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Scope</span></h3>
            <p class="card-text">This Website Privacy Policy applies to personal information collected by this website, which is hosted by 120ms.</p>
            <hr>
            <h3 class="card-title"><span class="fw-bold">What We Collect and Why</span></h3>
            <p class="card-text">This Privacy Policy pertains to personal information that identifies an individual, such as name, email address, and phone number.</p>
            <h4 class="card-subtitle mb-2"><span class="fw-bold">Information Collected</span></h4>
            <ol class="list-group">
                <li class="list-group-item">
                    <p><span class="fw-bold">Registration and Inquiry</span></p>
                    <ul class="list-group">
                        <li class="list-group-item">If you register or inquire about services, we collect basic information such as your name, email, and phone number.</li>
                    </ul>
                </li>
                <li class="list-group-item">
                    <p><span class="fw-bold">Auto-Collected Information</span></p>
                    <ul class="list-group">
                        <li class="list-group-item">Data such as Internet domain, IP address, date/time of visit, and pages accessed are automatically collected. This helps us analyze usage trends and improve services after anonymizing user-identifiable data.</li>
                    </ul>
                </li>
            </ol>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Cookies</span></h3>
            <p class="card-text">Cookies are files transferred to users’ devices to enhance site functionality and deliver personalized services.</p>
            <ul class="list-group">
                <li class="list-group-item">We use persistent cookies to analyze web traffic and search engine patterns.</li>
                <li class="list-group-item">Information from cookies is aggregated and not used to track individual users.</li>
            </ul>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Security</span></h3>
            <p class="card-text">120ms has implemented reasonable physical, technical, and administrative safeguards to protect your personal information from unauthorized access or use.</p>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Sharing Your Information</span></h3>
            <p class="card-text">Your information will only be shared:</p>
            <ol class="list-group list-group-numbered">
                <li class="list-group-item">As permitted or required by law.</li>
                <li class="list-group-item">To protect 120ms's interests.</li>
                <li class="list-group-item">With service providers acting on our behalf, who agree to protect the confidentiality of your data.</li>
            </ol>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Disclaimer and Links to Other Websites</span></h3>
            <p class="card-text">Our website may contain links to third-party websites. 120ms is not responsible for the privacy practices of these sites. Please review their privacy policies before submitting personal data.</p>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Changes to This Policy</span></h3>
            <p class="card-text">This Privacy Policy is effective as of December 11, 2024, and may be updated from time to time. Updates will take effect immediately upon posting. Continued use of the website constitutes acknowledgment of and consent to any modifications.</p>
            <p class="card-text">For material changes, we will notify you via email or through a prominent website notice.</p>
            <hr>
            <h3 class="card-title"><span class="fw-bold">Contact Information</span></h3>
            <p class="card-text"><span class="fw-bold">The Data Protection Officer</span><br>Email: <a href="mailto:winston.agustin@lpunetwork.edu.ph">winston.agustin@lpunetwork.edu.ph</a></p>
            <p class="card-text"><span class="fw-bold">Effective Date:</span> December 11, 2024<br><span class="fw-bold">Version:</span> 1</p>
        </div>
    </div>
</div>
<?php
include '../assets/layouts/footer.php';
?>