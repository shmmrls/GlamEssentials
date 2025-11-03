<?php
?>
<footer class="site-footer" id="siteFooter">
  <p><?php echo date('Y'); ?> GlamEssentials. All rights reserved.</p>
</footer>
</body>
</html>

<style>

  body {
    margin: 0;
    font-family: Arial, sans-serif;
    padding-bottom: 60px; /* Space for fixed footer */
  }

  footer {
    background-color: #FFB6C1;
    color: #000000;
    text-align: center;
    padding: 15px 0;
    width: 100%;
    font-size: 14px;
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.1);
  }

  footer p {
    margin: 0;
    padding: 0 20px;
  }
</style>