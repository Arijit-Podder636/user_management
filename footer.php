<?php
// footer.php
?>
  </main>
  <!-- Image Modal -->
  <div id="imgModal" class="modal">
    <span class="modal-close">&times;</span>
    <img class="modal-content" id="modalImage">
  </div>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById("imgModal");
    const modalImg = document.getElementById("modalImage");
    const closeBtn = document.getElementsByClassName("modal-close")[0];

    document.querySelectorAll(".profile-pic").forEach(img => {
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
        };
    });

    closeBtn.onclick = function() {
        modal.style.display = "none";
    };

    modal.onclick = function(e) {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    };
  });
  </script>

</body>
</html>
