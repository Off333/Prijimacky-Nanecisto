        </div>  
        <footer id="sticky-footer" class="mt-<?php $_SERVER['SCRIPT_NAME'] == "/PrijimackyNanecisto/vypis.php" ? print(0) : print(4); ?> py-4 bg-dark text-white-50">
            <div class="container text-center">
            <small></small>
            </div>
        </footer>

        <!-- BOOTSTRAP -->
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <?php if(isset($endScript)) {echo "<script src=".$endScript."></script>";}?>
        <script>
            var url = document.location.href;
            var navElement = (url.substring(url.lastIndexOf('/') + 1)).split('.')[0];
            
            if(navElement == '' || navElement == null) {
                $("#index").addClass("active");
            } else {
                $("#"+navElement).addClass("active");
            }
        </script>
    </body>
</html>