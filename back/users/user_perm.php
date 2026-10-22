<?php
include("../../inc/connection.php");

// VALIDATE INPUT
if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
    die('<div class="alert alert-danger m-3"><i class="fas fa-exclamation-triangle mr-2"></i>Invalid User ID</div>');
}
$id = intval($_REQUEST['id']);

// FETCH USER DATA
$get_details = mysqli_query($connection, "SELECT user_id, username, permissions FROM kp_users WHERE user_id = $id LIMIT 1");
if (!$get_details || mysqli_num_rows($get_details) === 0) {
    die('<div class="alert alert-warning m-3"><i class="fas fa-user-times mr-2"></i>User not found</div>');
}
$row = mysqli_fetch_assoc($get_details);
$user_username = htmlspecialchars($row['username'] ?? 'Unknown');
$perm = $row['permissions'] ?? '';

// PERMISSION CHECK HELPER
function is_checked($perm_str, $menu_id) {
    if (empty($perm_str)) return '';
    $perms = array_map('trim', explode(',', $perm_str));
    return in_array((string)$menu_id, $perms) ? 'checked' : '';
}

// BUILD MENU TREE RECURSIVELY (ALL LEVELS WITH ROOT PARENT TRACKING)
function buildMenuTree($connection, $parent_id = 0, $root_parent_id = null, $level = 1) {
    $menu_html = '';
    $query = mysqli_query($connection, "SELECT menu_id, name FROM kp_menu WHERE parent = $parent_id ORDER BY menu_order");
    
    while ($item = mysqli_fetch_assoc($query)) {
        $menu_id = (int)$item['menu_id'];
        $name = htmlspecialchars($item['name']);
        $has_children = mysqli_query($connection, "SELECT 1 FROM kp_menu WHERE parent = $menu_id LIMIT 1");
        $children_exist = mysqli_num_rows($has_children) > 0;
        
        // Track root parent (level 1 menu_id) for entire branch
        $current_root_parent = ($level === 1) ? $menu_id : $root_parent_id;
        
        // Determine styling based on level
        $indent_class = '';
        $indent_style = '';
        $icon = '';
        $label_class = '';
        
        if ($level === 1) {
            $indent_class = '';
            $indent_style = '';
            $icon = 'fa-layer-group text-primary';
            $label_class = 'font-weight-bold';
        } elseif ($level === 2) {
            $indent_class = 'ml-3';
            $indent_style = 'padding-left: 1.5rem;';
            $icon = 'fa-folder text-success';
            $label_class = 'text-muted small';
        } elseif ($level === 3) {
            $indent_class = 'ml-4';
            $indent_style = 'padding-left: 2.5rem;';
            $icon = 'fa-file-alt text-info';
            $label_class = 'text-muted small';
        } elseif ($level === 4) {
            $indent_class = 'ml-5';
            $indent_style = 'padding-left: 3.5rem;';
            $icon = 'fa-caret-right text-muted';
            $label_class = 'text-muted small';
        }
        
        // Build menu item HTML
        $menu_html .= '<div class="permission-item ' . $indent_class . '" style="' . $indent_style . '">';
        $menu_html .= '  <div class="custom-control custom-checkbox">';
        $menu_html .= '    <input type="checkbox" id="menu_' . $menu_id . '" name="menu[]" value="' . $menu_id . '" ' . is_checked($GLOBALS['perm'], $menu_id) . ' class="custom-control-input ' . ($level === 1 ? 'parent-checkbox' : 'child-checkbox') . '" data-parent-id="' . $current_root_parent . '">';
        $menu_html .= '    <label class="custom-control-label ' . $label_class . '" for="menu_' . $menu_id . '">';
        $menu_html .= '      <i class="fas ' . $icon . ' mr-1"></i>' . $name;
        $menu_html .= '    </label>';
        $menu_html .= '  </div>';
        
        // Recursively build children
        if ($children_exist) {
            $menu_html .= buildMenuTree($connection, $menu_id, $current_root_parent, $level + 1);
        }
        
        $menu_html .= '</div>';
    }
    
    return $menu_html;
}

// COUNT TOP-LEVEL MENUS FOR LAYOUT
$top_menus = mysqli_query($connection, "SELECT menu_id, name FROM kp_menu WHERE level=1 AND parent=0 ORDER BY menu_order");
$menu_count = mysqli_num_rows($top_menus);
?>
<div class="modal-content modal-permissions">
    <div class="modal-header bg-gradient text-white py-3 px-4">
        <div>
            <h4 class="modal-title font-weight-bold d-flex align-items-center">
                <i class="fas fa-shield-alt mr-2"></i>
                Set Permissions for <span class="text-warning ml-1"><?php echo strtoupper($user_username); ?></span>
            </h4>
            <small class="text-light opacity-75">User ID: #<?php echo $id; ?></small>
        </div>
        <button type="button" class="close text-white opacity-100" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    
    <div class="modal-body p-0">
        <!-- ACTION BAR -->
        <div class="permission-actions bg-light py-2 px-3 border-bottom d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary mr-2" id="btn-select-all">
                    <i class="fas fa-check-double mr-1"></i>Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-deselect-all">
                    <i class="fas fa-ban mr-1"></i>Deselect All
                </button>
            </div>
            <div class="badge badge-pill badge-success py-2 px-3 mt-2 mt-md-0">
                <i class="fas fa-list-check mr-1"></i>
                <span id="permissionCount">0</span> permissions selected
            </div>
        </div>
        
        <div class="row m-0">
            <!-- PERMISSIONS PANEL -->
            <div class="col-lg-8 col-md-7 col-12 p-4 permission-container">
                <form id="perm-form" method="POST" action="perm_action.php">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    
                    <!-- MENU TREE (ALL LEVELS) -->
                    <div class="permission-tree">
                        <?php echo buildMenuTree($connection, 0, null, 1); ?>
                    </div>
                    
                    <!-- STICKY SAVE BUTTON -->
                    <div class="permission-footer sticky-bottom bg-white py-3 border-top mt-3">
                        <button type="submit" id="save" class="btn btn-lg btn-gradient-success shadow px-5 py-2" disabled>
                            <i class="fas fa-save mr-2"></i>SAVE PERMISSIONS
                        </button>
                        <div class="text-muted small mt-2">
                            <i class="fas fa-info-circle mr-1"></i>User must log out to apply changes
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- INFO PANEL -->
            <div class="col-lg-4 col-md-5 col-12 p-4 bg-light info-panel">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <div class="user-avatar mx-auto mb-3">
                            <span class="initials"><?php echo strtoupper(substr($user_username, 0, 2)); ?></span>
                        </div>
                        <h5 class="font-weight-bold mb-1"><?php echo $user_username; ?></h5>
                        <div class="badge badge-primary">System User</div>
                    </div>
                </div>
                
                <div class="alert alert-light border-primary mb-4">
                    <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-lightbulb mr-2"></i>Smart Permissions</h6>
                    <ul class="pl-3 mb-0 small">
                        <li class="mb-1">Parent items control all children below</li>
                        <li class="mb-1">Selecting any child auto-checks its parent</li>
                        <li class="mb-1">Deselecting parent clears all children</li>
                        <li>Save button activates instantly when permissions are selected</li>
                    </ul>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="font-weight-bold">Total Modules</span>
                            <span class="badge badge-primary"><?php echo $menu_count; ?></span>
                        </div>
                        <div class="progress rounded-pill" style="height: 8px;">
                            <div class="progress-bar bg-gradient" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-white rounded border">
                    <h6 class="font-weight-bold text-danger mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Critical Reminder</h6>
                    <p class="mb-0 small text-muted">
                        Never grant permissions beyond user's role. Changes apply immediately after save.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.modal-permissions { border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.15); }
.bg-gradient { background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); }
.btn-gradient-success { 
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%); 
    border: none; 
    font-weight: 600;
    transition: all 0.3s ease;
}
.btn-gradient-success:hover { 
    transform: translateY(-2px); 
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
    background: linear-gradient(135deg, #218838 0%, #1e9d8c 100%);
}
.permission-container { max-height: 65vh; overflow-y: auto; }
.permission-actions { box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.permission-tree { display: flex; flex-direction: column; gap: 0.6rem; }
.permission-item { transition: all 0.2s ease; border-radius: 4px; }
.permission-item:hover { background-color: rgba(78, 115, 223, 0.06); }
.permission-footer { box-shadow: 0 -2px 15px rgba(0,0,0,0.08); position: sticky; bottom: 0; z-index: 100; background: white; }
.info-panel .user-avatar { 
    width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%); 
    display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.5rem;
}
.info-panel .initials { display: inline-block; width: 100%; }
@media (max-width: 767px) {
    .modal-dialog { margin: 0.5rem; max-width: calc(100% - 1rem); }
    .permission-container, .info-panel { padding: 1rem !important; }
    .permission-footer .btn { width: 100%; }
    .permission-actions { flex-direction: column; align-items: stretch; }
    .permission-actions > div:first-child { width: 100%; margin-bottom: 0.5rem; }
}
</style>

<!-- SWEET ALERT 2 - CORRECTED CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ===== GLOBAL FUNCTIONS (REQUIRED FOR DYNAMIC CONTENT) =====
window.selectAllPermissions = function() {
    document.querySelectorAll('input[name="menu[]"]').forEach(cb => cb.checked = true);
    document.querySelectorAll('.parent-checkbox').forEach(p => {
        const children = document.querySelectorAll(`.child-checkbox[data-parent-id="${p.dataset.parentId}"]`);
        children.forEach(c => c.checked = true);
        p.checked = true;
    });
    updatePermissionUI();
    Swal.fire({ icon: 'success', title: 'All Selected!', text: 'Every permission enabled', timer: 1200, showConfirmButton: false });
};

window.deselectAllPermissions = function() {
    document.querySelectorAll('input[name="menu[]"]').forEach(cb => cb.checked = false);
    updatePermissionUI();
    Swal.fire({ icon: 'info', title: 'All Cleared!', text: 'Permissions deselected', timer: 1200, showConfirmButton: false });
};

// ===== CORE UI MANAGEMENT =====
function updatePermissionUI() {
    const count = document.querySelectorAll('input[name="menu[]"]:checked').length;
    const countEl = document.getElementById('permissionCount');
    const saveBtn = document.getElementById('save');
    
    if (countEl) countEl.textContent = count;
    if (saveBtn) {
        saveBtn.disabled = count === 0;
        saveBtn.classList.toggle('btn-secondary', count === 0);
        saveBtn.classList.toggle('btn-gradient-success', count > 0);
    }
}

// ===== EVENT DELEGATION (WORKS FOR DYNAMIC CONTENT) =====
document.addEventListener('click', function(e) {
    // Select All button
    if (e.target.closest('#btn-select-all')) {
        window.selectAllPermissions();
        return;
    }
    
    // Deselect All button
    if (e.target.closest('#btn-deselect-all')) {
        window.deselectAllPermissions();
        return;
    }
    
    // Parent checkbox handler
    const parentCheckbox = e.target.closest('.parent-checkbox');
    if (parentCheckbox && e.target.tagName === 'INPUT') {
        const children = document.querySelectorAll(`.child-checkbox[data-parent-id="${parentCheckbox.dataset.parentId}"]`);
        children.forEach(child => child.checked = parentCheckbox.checked);
        updatePermissionUI();
        return;
    }
    
    // Child checkbox handler
    const childCheckbox = e.target.closest('.child-checkbox');
    if (childCheckbox && e.target.tagName === 'INPUT') {
        const parent = document.querySelector(`.parent-checkbox[data-parent-id="${childCheckbox.dataset.parentId}"]`);
        if (parent) {
            const siblings = document.querySelectorAll(`.child-checkbox[data-parent-id="${childCheckbox.dataset.parentId}"]`);
            parent.checked = Array.from(siblings).some(sib => sib.checked);
        }
        updatePermissionUI();
        return;
    }
});

// ===== FORM SUBMISSION WITH JSON HANDLING =====
document.addEventListener('submit', async function(e) {
    if (e.target.id !== 'perm-form') return;
    e.preventDefault();
    
    const saveBtn = document.getElementById('save');
    if (!saveBtn || saveBtn.disabled) return;
    
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    saveBtn.disabled = true;
    
    try {
        const response = await fetch('perm_action.php', {
            method: 'POST',
            body: new FormData(e.target)
        });
        
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Permissions Updated!',
                text: data.message || 'Changes saved successfully',
                timer: 2000,
                showConfirmButton: false
            });
            
            setTimeout(() => {
                // Close modal (Bootstrap/jQuery compatible)
                const modalEl = document.getElementById('rec-modal');
                if (modalEl && typeof jQuery !== 'undefined') {
                    jQuery(modalEl).modal('hide');
                }
                // Refresh parent page
                if (window.parent?.location) window.parent.location.reload();
                else window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Save failed');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Save Failed',
            text: error.message || 'Unable to save permissions. Please try again.',
            confirmButtonColor: '#dc3545'
        });
        if (saveBtn) {
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        }
    }
});

// ===== INITIALIZE UI =====
document.addEventListener('DOMContentLoaded', updatePermissionUI);
// Fallback initialization for dynamically injected content
setTimeout(updatePermissionUI, 150);
</script>