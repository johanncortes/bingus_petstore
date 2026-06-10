    <!-- Scripts globales -->
    <script src="/bingus_petstore/assets/js/api.js"></script>
    
    <!-- Script específico de la página (si existe) -->
    <?php if (isset($page_script)): ?>
        <script src="/bingus_petstore/assets/js/<?php echo $page_script; ?>"></script>
    <?php endif; ?>
</body>
</html>
