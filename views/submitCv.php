<?php ob_start(); ?>
<?php $baseUrl = '/skillbox/public'; ?>

<section class="about-section py-5" style="background: #f4f7f9;">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7 col-md-8">
        <div class="card shadow-lg rounded-4 p-5" style="border: none;">
          <h2 class="text-center mb-4" style="font-weight: 700; color: #1F3440;">
            <?= isset($isEdit) && $isEdit ? 'Edit Portfolio' : 'About You' ?>
          </h2>
          
          <form action="<?= isset($isEdit) && $isEdit ? $baseUrl . '/portfolio/update/' . $portfolio['id'] : $baseUrl . '/portfolio/store' ?>" 
                method="POST" 
                enctype="multipart/form-data">
            
            <!-- Full Name -->
            <div class="mb-3">
              <label for="fullname" class="form-label fw-semibold">Full Name</label>
              <input type="text" 
                     name="fullname" 
                     class="form-control rounded-3 border-0 shadow-sm" 
                     id="fullname" 
                     placeholder="Enter your full name" 
                     value="<?= isset($portfolio) ? htmlspecialchars($portfolio['full_name']) : '' ?>"
                     required>
            </div>

            <!-- Email -->
            <div class="mb-3">
              <label for="email" class="form-label fw-semibold">Email</label>
              <input type="email" 
                     name="email" 
                     class="form-control rounded-3 border-0 shadow-sm" 
                     id="email" 
                     placeholder="Enter your email"
                     value="<?= isset($portfolio) ? htmlspecialchars($portfolio['email']) : '' ?>"
                     required>
            </div>

            <!-- Phone Number -->
            <div class="mb-3">
              <label for="phone" class="form-label fw-semibold">Phone Number</label>
              <input type="tel" 
                     name="phone" 
                     class="form-control rounded-3 border-0 shadow-sm" 
                     id="phone" 
                     placeholder="Enter your phone number"
                     value="<?= isset($portfolio) ? htmlspecialchars($portfolio['phone']) : '' ?>"
                     required>
            </div>

            <!-- Address -->
            <div class="mb-3">
              <label for="address" class="form-label fw-semibold">Address</label>
              <input type="text" 
                     name="address" 
                     class="form-control rounded-3 border-0 shadow-sm" 
                     id="address" 
                     placeholder="Enter your address"
                     value="<?= isset($portfolio) ? htmlspecialchars($portfolio['address']) : '' ?>"
                     required>
            </div>

            <!-- LinkedIn -->
            <div class="mb-3">
              <label for="linkedin" class="form-label fw-semibold">LinkedIn</label>
              <input type="url" 
                     name="linkedin" 
                     class="form-control rounded-3 border-0 shadow-sm" 
                     id="linkedin" 
                     placeholder="Enter your LinkedIn profile"
                     value="<?= isset($portfolio) ? htmlspecialchars($portfolio['linkedin']) : '' ?>">
            </div>

            <!-- Attachment -->
            <div class="mb-4">
              <label for="attachment" class="form-label fw-semibold">
                Attachment (PDF) 
                <?= isset($portfolio) && $portfolio['attachment_path'] ? '- <span class="text-muted">Current: ' . basename($portfolio['attachment_path']) . '</span>' : '' ?>
              </label>
              <input type="file" 
                     name="attachment" 
                     class="form-control rounded-3" 
                     id="attachment" 
                     accept=".pdf">
              <?php if (isset($isEdit) && $isEdit): ?>
                <small class="text-muted">Leave empty to keep current file</small>
              <?php endif; ?>
            </div>

            <!-- Role -->
            <div class="mb-4">
              <label class="form-label fw-semibold d-block">Select Your Role</label>
              <select name="requested_role" class="form-select" required>
                  <option value="">-- Select Role --</option>
                  <?php foreach ($roles as $role): ?>
                      <option value="<?= htmlspecialchars($role['id']) ?>"
                          <?php 
                          // For edit mode, check if this role matches the portfolio's requested role
                          if (isset($portfolio) && isset($portfolio['requested_role_id'])) {
                              echo $portfolio['requested_role_id'] == $role['id'] ? 'selected' : '';
                          }
                          ?>>
                          <?= htmlspecialchars(ucfirst($role['name'])) ?>
                      </option>
                  <?php endforeach; ?>
              </select>
            </div>

            <!-- Submit Button -->
            <div class="d-grid">
              <button type="submit" class="btn btn-gradient btn-lg fw-bold py-2" style="
                background: linear-gradient(135deg, #25BDB0 0%, #2C6566 100%);
                color: white;
                border-radius: 50px;
                transition: transform 0.2s, box-shadow 0.2s;
              " onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.2)';" 
                 onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                <?= isset($isEdit) && $isEdit ? 'Update' : 'Send' ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
$title = isset($isEdit) && $isEdit ? "SkillBox - Edit Portfolio" : "SkillBox - Submit CV";
require __DIR__ . '/layouts/main.php';