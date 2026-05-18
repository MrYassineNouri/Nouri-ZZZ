<?php // includes/footer.php ?>
<footer>
    <div class="container text-center">
        <p class="mb-1" style="font-family:'Playfair Display',serif;font-size:1.2rem;color:#e07b39;">Nouri'ZZZ</p>
        <p class="small mb-0">123 Gourmet Street &bull; contact@labelletable.com &bull; +1 555 123 4567</p>
        <p class="small mt-2" style="color:#7a6a5a;">&copy; <?= date('Y') ?> Nouri'ZZZ. All rights reserved.</p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?= isset($extraScript) ? $extraScript : '' ?>
</body>
</html>
