<?php if (isset($_SESSION['auth'])) { ?>

    </body>

    <footer id="myFooter">
        <div class="container">
            <div class="row">
                <div class="col-sm-3">
                    <h2 class="logo">
                        <a href="../home/" target="_blank">
                            <img src="../assets/images/<?php echo APP_LOGO_FOOTER; ?>" alt="" width="50%" height="50%" class="">
                        </a>
                    </h2>
                </div>
                <div class="col-sm-6">
                    <h5>Atlas</h5>
                    <ul>
                        <p>This project is licensed under the MIT License 2024. <a href="../privacy"><strong>Privacy policy</strong></a></p>
                    </ul>
                </div>
            
                <div class="col-sm-3 my-3">
                    <a class="btn btn-default" href="mailto:ton.agustin09@gmail.com" target="_blank">Email Us!</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            <p>
                <a href="#" target="_blank"><?php echo APP_NAME; ?></a> |
                <a href="#" target="_blank"><?php echo APP_ORGANIZATION; ?></a> |
                <a href="#" target="_blank"><?php echo APP_OWNER; ?></a>
            </p>
        </div>
    </footer>

<?php } ?> 


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Bootstrap Datepicker CSS -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">

<!-- Bootstrap Datepicker JS -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<!-- Moments JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>

<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Summernote JS (only) -->
<?php if (isset($_SESSION['usertype']) && $_SESSION['usertype'] == 0): ?>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<?php endif; ?>

<?php if (isset($_SESSION['auth'])) { ?>

    <script src="../assets/js/check_inactive.js"></script>

<?php } ?>


</body>

</html>

<?php

if (isset($_SESSION['ERRORS']))
    $_SESSION['ERRORS'] = NULL;
if (isset($_SESSION['STATUS']))
    $_SESSION['STATUS'] = NULL;

?>