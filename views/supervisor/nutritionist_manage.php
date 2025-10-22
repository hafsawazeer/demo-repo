<?php
// Include required files
require_once __DIR__ . '/../../controllers/SupervisorController.php';

// Initialize variables
$search = isset($_GET['q']) ? $_GET['q'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at_desc';
$view_nutritionist = null;
$success_message = '';
$error_message = '';
$nutritionists = [];

// Handle view request first
if (isset($_GET['view'])) {
    $view_nutritionist = SupervisorController::getNutritionistById($_GET['view']);
}

// Handle POST requests (add, edit, update status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_nutritionist':
                $result = SupervisorController::addNutritionist($_POST);
                if ($result['success']) {
                    $success_message = "Nutritionist added successfully.";
                } else {
                    $error_message = $result['message'];
                }
                break;

            case 'edit_nutritionist':
                $result = SupervisorController::editNutritionist($_POST);
                if ($result['success']) {
                    $success_message = "Nutritionist updated successfully.";
                } else {
                    $error_message = $result['message'];
                }
                break;
        }
    } elseif (isset($_POST['update_status'])) {
        $result = SupervisorController::updateNutritionistStatus($_POST['nutritionist_id'], $_POST['status']);
        if ($result['success']) {
            $success_message = "Nutritionist status updated successfully.";
            // Redirect to avoid form resubmission
            header("Location: nutritionist_manage.php?view=" . $_POST['nutritionist_id']);
            exit();
        } else {
            $error_message = $result['message'];
        }
    }
}

// Handle GET requests (delete)
if (isset($_GET['delete'])) {
    $result = SupervisorController::deleteNutritionist($_GET['delete']);
    if ($result['success']) {
        $success_message = "Nutritionist deleted successfully.";
    } else {
        $error_message = $result['message'];
    }
}

// Fetch nutritionists and statistics
$nutritionists = SupervisorController::getNutritionists($search, $sort); 
$statistics = SupervisorController::getNutritionistStatistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FitVerse - Manage Nutritionists</title>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.364.0/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/supervisor_manage.css" />
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">
            <a href="../common/home.html" style="text-decoration: none; color: inherit;">
                FIT<span>VERSE</span>
            </a>
        </div>
        <nav class="nav-links">
            <a href="/FitVerse/app/views/shop/index.html">
                <img src="/FitVerse/app/views/icons/shop-icon.svg" alt="Shop Icon" 
                    style="width:18px; height:18px; vertical-align:middle; margin-right:6px; filter: brightness(0);">
            Shop
            </a>
            <button class="profile-btn" aria-label="Profile">
                <span class="dot"></span>
                <span>Profile</span>
            </button>
        </nav>
    </header>

    <main class="layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <nav class="nav-menu">
                <a href="sup_dashboard.php" class="nav-item">
                    <span class="icon" data-lucide="layout-grid"></span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="#" class="nav-item active">
                    <span class="icon" data-lucide="users"></span>
                    <span class="nav-text">Manage Nutritionists</span>
                </a>
                <a href="trainer_manage.php" class="nav-item">
                    <span class="icon" data-lucide="dumbbell"></span>
                    <span class="nav-text">Manage Trainers</span>
                </a>
                <a href="assign_clients.html" class="nav-item">
                    <span class="icon" data-lucide="calendar"></span>
                    <span class="nav-text">Assign clients</span>
                </a>
                <a href="../common/logout.php" class="nav-item">
                    <span class="icon" data-lucide="log-out"></span>
                    <span class="nav-text">Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <section class="content">
            <div class="page-header">
                <h1 class="page-title">Manage Nutritionists</h1>
                <button class="add-btn" onclick="openAddModal()">
                    <span class="icon" data-lucide="plus"></span>
                    Add Nutritionist
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe;">
                        <span class="icon" data-lucide="users" style="stroke: #2563eb;"></span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $statistics['total']; ?></div>
                        <div class="stat-label">Total Nutritionists</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7;">
                        <span class="icon" data-lucide="clock" style="stroke: #f59e0b;"></span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $statistics['pending']; ?></div>
                        <div class="stat-label">Pending Approval</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #d1fae5;">
                        <span class="icon" data-lucide="check-circle" style="stroke: #10b981;"></span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $statistics['active']; ?></div>
                        <div class="stat-label">Active</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fee2e2;">
                        <span class="icon" data-lucide="x-circle" style="stroke: #ef4444;"></span>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $statistics['inactive']; ?></div>
                        <div class="stat-label">Inactive</div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Sort Toolbar -->
            <div class="toolbar">
                <form method="GET" action="" style="display:flex; gap:16px; align-items:center; width:100%;">
                    <div class="search" style="flex:1;">
                        <span class="search-icon">🔎</span>
                        <input type="text" name="q" id="searchInput" placeholder="Search by name, email, NIC or specialization" value="<?php echo htmlspecialchars($search); ?>" />
                    </div>
                    <label class="sort">
                        <span>Sort by:</span>
                        <select id="sortSelect" name="sort">
                            <option value="created_at_desc" <?php echo $sort==='created_at_desc'?'selected':''; ?>>Newest First</option>
                            <option value="created_at_asc" <?php echo $sort==='created_at_asc'?'selected':''; ?>>Oldest First</option>
                            <option value="name_asc" <?php echo $sort==='name_asc'?'selected':''; ?>>Name (A–Z)</option>
                            <option value="name_desc" <?php echo $sort==='name_desc'?'selected':''; ?>>Name (Z–A)</option>
                            <option value="experience_desc" <?php echo $sort==='experience_desc'?'selected':''; ?>>Experience (High → Low)</option>
                            <option value="experience_asc" <?php echo $sort==='experience_asc'?'selected':''; ?>>Experience (Low → High)</option>
                            <option value="status" <?php echo $sort==='status'?'selected':''; ?>>Status</option>
                        </select>
                    </label>
                    <button type="submit" style="display:none;">Apply</button>
                </form>
            </div>

            <!-- Nutritionists Table -->
            <div class="card">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Specialization</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th class="center-th">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($nutritionists)): ?>
                            <?php foreach ($nutritionists as $index => $nutritionist): ?>
                                <tr>
                                    <td><?php echo str_pad($index + 1, 4, '0', STR_PAD_LEFT); ?>.</td>
                                    <td>NT<?php echo str_pad($nutritionist['nutritionist_id'], 5, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($nutritionist['name']); ?></td>
                                    <td><?php echo htmlspecialchars($nutritionist['email']); ?></td>
                                    <td><?php echo htmlspecialchars($nutritionist['contact_no']); ?></td>
                                    <td><?php echo htmlspecialchars($nutritionist['specialization']); ?></td>
                                    <td><?php echo htmlspecialchars($nutritionist['experience']); ?> years</td>
                                    <td>
                                        <span class="status-badge status-<?php echo $nutritionist['status']; ?>">
                                            <?php echo ucfirst($nutritionist['status']); ?>
                                        </span>
                                    </td>
                                    <td class="controls-cell">
                                        <a href="?view=<?php echo $nutritionist['nutritionist_id']; ?>&q=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="link action-btn view-btn">
                                            View Details
                                        </a>
                                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($nutritionist)); ?>)" class="link action-btn edit-btn">Edit</button>
                                        <a href="?delete=<?php echo $nutritionist['nutritionist_id']; ?>&q=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="link action-btn delete" onclick="return confirmDelete('<?php echo htmlspecialchars($nutritionist['name']); ?>')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: var(--muted);">
                                    <?php if (!empty($search)): ?>
                                        No nutritionists found matching "<?php echo htmlspecialchars($search); ?>". Try adjusting your search.
                                    <?php else: ?>
                                        No nutritionists registered yet.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Add Nutritionist Modal -->
    <div id="addNutritionistModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Add New Nutritionist</h2>
                <button class="close-btn" onclick="closeAddModal()">
                    <span class="icon" data-lucide="x"></span>
                </button>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="modal-form">
                <input type="hidden" name="action" value="add_nutritionist">
                
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_name">Full Name *</label>
                            <input type="text" id="add_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="add_email">Email *</label>
                            <input type="email" id="add_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="add_phone">Phone *</label>
                            <input type="tel" id="add_phone" name="contact_no" required>
                        </div>
                        <div class="form-group">
                            <label for="add_gender">Gender *</label>
                            <select id="add_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="add_dob">Date of Birth *</label>
                            <input type="date" id="add_dob" name="dob" required>
                        </div>
                        <div class="form-group">
                            <label for="add_nic">NIC Number *</label>
                            <input type="text" id="add_nic" name="nic" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Professional Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_specialization">Specialization *</label>
                            <input type="text" id="add_specialization" name="specialization" required>
                        </div>
                        <div class="form-group">
                            <label for="add_experience">Experience (Years) *</label>
                            <input type="number" id="add_experience" name="experience" min="0" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="add_certification">Certification</label>
                            <input type="text" id="add_certification" name="certification">
                        </div>
                        <div class="form-group full-width">
                            <label for="add_qualifications">Qualifications</label>
                            <textarea id="add_qualifications" name="qualifications" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Account Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_password">Password *</label>
                            <input type="password" id="add_password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="add_confirm_password">Confirm Password *</label>
                            <input type="password" id="add_confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Documents</h3>
                    <div class="form-group">
                        <label for="add_nic_image">NIC Image</label>
                        <input type="file" id="add_nic_image" name="nic_image" accept="image/*,application/pdf">
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Nutritionist</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Nutritionist Modal -->
    <div id="editNutritionistModal" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Edit Nutritionist</h2>
                <button class="close-btn" onclick="closeEditModal()">
                    <span class="icon" data-lucide="x"></span>
                </button>
            </div>
            
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="edit_nutritionist">
                <input type="hidden" id="edit_nutritionist_id" name="nutritionist_id">
                
                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_name">Full Name *</label>
                            <input type="text" id="edit_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_email">Email *</label>
                            <input type="email" id="edit_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_phone">Phone *</label>
                            <input type="tel" id="edit_phone" name="contact_no" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Professional Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="edit_specialization">Specialization *</label>
                            <input type="text" id="edit_specialization" name="specialization" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_experience">Experience (Years) *</label>
                            <input type="number" id="edit_experience" name="experience" min="0" required>
                        </div>
                        <div class="form-group full-width">
                            <label for="edit_certification">Certification</label>
                            <input type="text" id="edit_certification" name="certification">
                        </div>
                        <div class="form-group full-width">
                            <label for="edit_qualifications">Qualifications</label>
                            <textarea id="edit_qualifications" name="qualifications" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Nutritionist</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Details Modal -->
    <?php if (!empty($view_nutritionist)): ?>
    <div id="viewDetailsModal" class="modal" style="display: block;">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Nutritionist Details</h2>
                <a href="nutritionist_manage.php?q=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sort); ?>" class="close-btn">
                    <span class="icon" data-lucide="x"></span>
                </a>
            </div>

            <div class="details-container">
                <!-- Personal Information -->
                <div class="details-section">
                    <h3>Personal Information</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Full Name:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['name']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nutritionist ID:</span> 
                            <span class="detail-value">NT<?= str_pad($view_nutritionist['nutritionist_id'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['email']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Phone:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['contact_no']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Gender:</span> 
                            <span class="detail-value"><?= htmlspecialchars(ucfirst($view_nutritionist['gender'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date of Birth:</span> 
                            <span class="detail-value"><?= date('F d, Y', strtotime($view_nutritionist['dob'])) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">NIC:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['nic']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Registration Date:</span> 
                            <span class="detail-value"><?= date('F d, Y', strtotime($view_nutritionist['created_at'])) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="details-section">
                    <h3>Professional Information</h3>
                    <div class="details-grid">
                        <div class="detail-item">
                            <span class="detail-label">Specialization:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['specialization']) ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Experience:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['experience']) ?> years</span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Certification:</span> 
                            <span class="detail-value"><?= htmlspecialchars($view_nutritionist['certification'] ?: 'Not provided') ?></span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Qualifications:</span> 
                            <span class="detail-value"><?= nl2br(htmlspecialchars($view_nutritionist['qualifications'] ?: 'Not provided')) ?></span>
                        </div>
                    </div>
                </div>

                <!-- NIC Document -->
                <div class="details-section">
                    <h3>NIC Document</h3>
                    <?php if (!empty($view_nutritionist['nic_image_path'])): 
                        $fileExt = strtolower(pathinfo($view_nutritionist['nic_image_path'], PATHINFO_EXTENSION));
                        $imagePath = '../../' . $view_nutritionist['nic_image_path'];
                    ?>
                        <?php if (in_array($fileExt, ['jpg','jpeg','png'])): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="NIC Image" class="nic-image" />
                            <a href="<?= htmlspecialchars($imagePath) ?>" target="_blank" class="btn btn-secondary" style="margin-top:12px;">
                                <span class="icon" data-lucide="external-link"></span> Open in New Tab
                            </a>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($imagePath) ?>" target="_blank" class="btn btn-secondary">
                                <span class="icon" data-lucide="file-text"></span> View PDF Document
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p style="color: var(--muted);">No NIC image uploaded</p>
                    <?php endif; ?>
                </div>

                <!-- Status Management -->
                <div class="details-section">
                    <h3>Status Management</h3>
                    <form method="POST" action="" class="status-form">
                        <input type="hidden" name="nutritionist_id" value="<?= $view_nutritionist['nutritionist_id'] ?>">
                        <div class="status-selector">
                            <label class="status-option">
                                <input type="radio" name="status" value="pending" <?= $view_nutritionist['status'] === 'pending' ? 'checked' : '' ?>> 
                                <span class="status-badge status-pending">Pending</span>
                            </label>
                            <label class="status-option">
                                <input type="radio" name="status" value="active" <?= $view_nutritionist['status'] === 'active' ? 'checked' : '' ?>> 
                                <span class="status-badge status-active">Active</span>
                            </label>
                            <label class="status-option">
                                <input type="radio" name="status" value="inactive" <?= $view_nutritionist['status'] === 'inactive' ? 'checked' : '' ?>> 
                                <span class="status-badge status-inactive">Inactive</span>
                            </label>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">
                            <span class="icon" data-lucide="save"></span> Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-left">
            <div class="logo">FIT<span>VERSE</span></div>
            <div class="copyright">Copyright © 2025 Fitverse. | All rights reserved.</div>
        </div>
        <div class="footer-links">
            <a href="#">Terms of Use</a>
            <a href="#">Privacy Policy</a>
        </div>
    </footer>

    <script src="../../assets/js/nutritionist_manage.js"></script>
</body>
</html>