<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a supervisor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'supervisor') {
    header("Location: login.html");
    exit();
}

try {
    // Get user details using prepared statement
    $sql = "SELECT * FROM admin_db WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception("User not found");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href='login.html';
          </script>";
    exit();
}

// Function to safely encode JSON data
function safeJsonEncode($data) {
    return htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
    <!-- Add Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="fixed w-64 h-screen bg-gray-800 text-white">
            <div class="p-6 border-b border-gray-700">
                <div class="w-20 h-20 rounded-full bg-pink-600 mx-auto mb-4 flex items-center justify-center text-3xl">
                    <?php echo strtoupper(substr($user['name_tbl'], 0, 1)); ?>
                </div>
                <div class="text-xl text-center"><?php echo htmlspecialchars($user['name_tbl']); ?></div>
                <div class="text-sm text-gray-400 text-center">Supervisor</div>
            </div>
            
            <nav class="mt-6">
                <a href="#recipe" onclick="showSection('recipe')" 
                   class="nav-item flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-book w-6"></i>
                    <span>Recipe Management</span>
                </a>
                <a href="#production" onclick="showSection('production')" 
                   class="nav-item flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-industry w-6"></i>
                    <span>Production Schedule</span>
                </a>
                <a href="#batch" onclick="showSection('batch')" 
                   class="nav-item flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-tasks w-6"></i>
                    <span>Batch Reports</span>
                </a>
            </nav>

            <a href="logout.php" onclick="return confirm('Are you sure you want to logout?');" 
               class="absolute bottom-0 w-full px-6 py-4 bg-pink-600 hover:bg-pink-700 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="ml-64 flex-1 p-8">
            <!-- Recipe Management Section -->
            <div id="recipe-section" class="content-section">
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-2xl font-semibold">Recipe Management</h2>
                    <button onclick="showRecipeModal()" 
                            class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Add New Recipe
                    </button>
                </div>

                <!-- Recipe Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">Ingredient Name</th>
                                <th class="px-6 py-3 text-left">Quantity</th>
                                <th class="px-6 py-3 text-left">Preparation Steps</th>
                                <th class="px-6 py-3 text-left">Equipment</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $recipe_sql = "SELECT * FROM recipe_db ORDER BY created_at DESC";
                            $stmt = $conn->prepare($recipe_sql);
                            $stmt->execute();
                            $recipe_result = $stmt->get_result();
                            
                            if ($recipe_result->num_rows > 0) {
                                while($row = $recipe_result->fetch_assoc()) {
                                    echo "<tr class='hover:bg-gray-50'>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['ingredient_name_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['quantity_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . nl2br(htmlspecialchars($row['preparation_step_tbl'])) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['equipment_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>
                                            <button onclick='editRecipe(" . safeJsonEncode($row) . ")' 
                                                    class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors mr-2'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                            <button onclick='deleteRecipe(" . $row['ingredient_id'] . ")' 
                                                    class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors'>
                                                <i class='fas fa-trash'></i> Delete
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500'>No recipes found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recipe Modal -->
                <div id="recipeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
                    <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Add/Edit Recipe</h3>
                            <button onclick="closeRecipeModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="recipeForm" action="process_recipe.php" method="POST" class="space-y-4">
                            <input type="hidden" id="ingredient_id" name="ingredient_id">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ingredient Name</label>
                                <input type="text" id="ingredient_name" name="ingredient_name_tbl" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                <input type="text" id="quantity" name="quantity_tbl" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Preparation Steps</label>
                                <textarea id="preparation_step" name="preparation_step_tbl" rows="3" required
                                          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
                                <input type="text" id="equipment" name="equipment_tbl" required
                                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="closeRecipeModal()" 
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors">
                                    Save Recipe
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Production Schedule Section -->
            <div id="production-section" class="content-section hidden">
                <div class="mb-6 flex justify-between items-center">
                    <h2 class="text-2xl font-semibold">Production Schedule</h2>
                    <button onclick="showScheduleModal()" 
                            class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        Add New Schedule
                    </button>
                </div>

                <!-- Production Schedule Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">Order Volume</th>
                                <th class="px-6 py-3 text-left">Production Capacity</th>
                                <th class="px-6 py-3 text-left">Staff Availability</th>
                                <th class="px-6 py-3 text-left">Equipment Status</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $schedule_sql = "SELECT * FROM production_db ORDER BY created_at DESC";
                            $stmt = $conn->prepare($schedule_sql);
                            $stmt->execute();
                            $schedule_result = $stmt->get_result();
                            
                            if ($schedule_result->num_rows > 0) {
                                while($row = $schedule_result->fetch_assoc()) {
                                    $status_class = match($row['equipment_status_tbl']) {
                                        'operational' => 'text-green-600',
                                        'maintenance' => 'text-yellow-600',
                                        'repair' => 'text-red-600',
                                        default => 'text-gray-600'
                                    };
                                    
                                    echo "<tr class='hover:bg-gray-50'>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['order_volumn_tbl']) . " units</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['capacity_tbl']) . " units/hour</td>";
                                    echo "<td class='px-6 py-4'>" . nl2br(htmlspecialchars($row['staff_availability_tbl'])) . "</td>";
                                    echo "<td class='px-6 py-4 {$status_class}'>" . htmlspecialchars($row['equipment_status_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>
                                            <button onclick='editSchedule(" . safeJsonEncode($row) . ")' 
                                                    class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors mr-2'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                            <button onclick='deleteSchedule(" . $row['production_id'] . ")' 
                                                    class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors'>
                                                <i class='fas fa-trash'></i> Delete
                                            </button>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500'>No schedules found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Schedule Modal -->
                <div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
                    <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Add/Edit Production Schedule</h3>
                            <button onclick="closeScheduleModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <form id="scheduleForm" action="process_schedule.php" method="POST" class="space-y-4">
                            <input type="hidden" id="production_id" name="production_id">
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Order Volume (units)</label>
                                <input type="number" id="order_volume" name="order_volumn_tbl" required
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-pink-500">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Production Capacity (units/hour)</label>
                                <input type="number" id="capacity" name="capacity_tbl" required
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-pink-500">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Staff Availability</label>
                                <textarea id="staff_availability" name="staff_availability_tbl" rows="3" required
                                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-pink-500"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Equipment Status</label>
                                <select id="equipment_status" name="equipment_status_tbl" required
                                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-pink-500">
                                    <option value="">Select Status</option>
                                    <option value="operational">Operational</option>
                                    <option value="maintenance">Under Maintenance</option>
                                    <option value="repair">Needs Repair</option>
                                </select>
                            </div>
                            
                            <div class="flex justify-end gap-2">
                                <button type="button" onclick="closeScheduleModal()" 
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors">
                                    Save Schedule
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Batch Reports Section -->
            <div id="batch-section" class="content-section hidden">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Batch Reports</h2>
                    
                    <!-- Filters -->
                    <div class="flex gap-4 mt-4">
                        <select id="statusFilter" onchange="filterBatches()" 
                                class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="">All Stages</option>
                            <option value="preparation">Preparation</option>
                            <option value="mixing">Mixing</option>
                            <option value="baking">Baking</option>
                            <option value="cooling">Cooling</option>
                            <option value="packaging">Packaging</option>
                        </select>
                        <input type="date" id="dateFilter" onchange="filterBatches()"
                               class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>
                </div>

                <!-- Batch Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Active Batches -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                <i class="fas fa-sync-alt text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Active Batches</h3>
                                <?php
                                $active_sql = "SELECT COUNT(*) as count FROM batch_db WHERE status_tbl = 'In Progress'";
                                $active_result = $conn->query($active_sql);
                                $active_count = $active_result->fetch_assoc()['count'];
                                ?>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $active_count; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Completed Today -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600">
                                <i class="fas fa-check-circle text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Completed Today</h3>
                                <?php
                                $today = date('Y-m-d');
                                $completed_sql = "SELECT COUNT(*) as count FROM batch_db 
                                                WHERE status_tbl = 'Completed' 
                                                AND DATE(endDate_tbl) = '$today'";
                                $completed_result = $conn->query($completed_sql);
                                $completed_count = $completed_result->fetch_assoc()['count'];
                                ?>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $completed_count; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Average Completion Time -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                                <i class="fas fa-clock text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-gray-500 text-sm">Average Completion Time</h3>
                                <?php
                                $avg_sql = "SELECT AVG(TIMESTAMPDIFF(HOUR, startDate_tbl, endDate_tbl)) as avg_hours 
                                           FROM batch_db WHERE endDate_tbl IS NOT NULL";
                                $avg_result = $conn->query($avg_sql);
                                $avg_hours = round($avg_result->fetch_assoc()['avg_hours'], 1);
                                ?>
                                <p class="text-2xl font-semibold text-gray-800"><?php echo $avg_hours; ?> hrs</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Batch Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">Batch No</th>
                                <th class="px-6 py-3 text-left">Start Date</th>
                                <th class="px-6 py-3 text-left">End Date</th>
                                <th class="px-6 py-3 text-left">Production Stage</th>
                                <th class="px-6 py-3 text-left">Quality Check</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $batch_sql = "SELECT * FROM batch_db ORDER BY startDate_tbl DESC";
                            $batch_result = $conn->query($batch_sql);
                            
                            if ($batch_result->num_rows > 0) {
                                while($row = $batch_result->fetch_assoc()) {
                                    $status_class = match($row['status_tbl']) {
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'In Progress' => 'bg-yellow-100 text-yellow-800',
                                        'Scheduled' => 'bg-blue-100 text-blue-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                    
                                    echo "<tr class='hover:bg-gray-50'>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['batch_no_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . date('Y-m-d H:i', strtotime($row['startDate_tbl'])) . "</td>";
                                    echo "<td class='px-6 py-4'>" . ($row['endDate_tbl'] ? date('Y-m-d H:i', strtotime($row['endDate_tbl'])) : '-') . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['production_stage_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . nl2br(htmlspecialchars($row['quality_check_tbl'])) . "</td>";
                                    echo "<td class='px-6 py-4'>
                                            <span class='px-2 py-1 rounded-full text-xs font-medium {$status_class}'>
                                                {$row['status_tbl']}
                                            </span>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='px-6 py-4 text-center text-gray-500'>No batch records found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Update JavaScript to handle Tailwind classes
    function showSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.add('hidden');
        });
        
        // Remove active class from all nav items
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('bg-pink-600');
            // Also remove hover effect when active
            item.classList.remove('hover:bg-gray-700');
        });
        
        // Show selected section
        document.getElementById(sectionName + '-section').classList.remove('hidden');
        
        // Add active class to clicked nav item
        const activeNav = document.querySelector(`[href="#${sectionName}"]`);
        activeNav.classList.add('bg-pink-600');
        // Remove hover effect when active
        activeNav.classList.remove('hover:bg-gray-700');
    }

    // Show recipe section by default and activate its nav item
    document.addEventListener('DOMContentLoaded', function() {
        showSection('recipe');
    });

    // Modal functions updated for Tailwind
    function showRecipeModal(recipe = null) {
        document.getElementById('recipeModal').classList.remove('hidden');
        const form = document.getElementById('recipeForm');
        form.reset();
        
        if (recipe) {
            // Populate form with recipe data for editing
            document.getElementById('ingredient_id').value = recipe.ingredient_id;
            document.getElementById('ingredient_name').value = recipe.ingredient_name_tbl;
            document.getElementById('quantity').value = recipe.quantity_tbl;
            document.getElementById('preparation_step').value = recipe.preparation_step_tbl;
            document.getElementById('equipment').value = recipe.equipment_tbl;
        } else {
            // Clear form for new recipe
            document.getElementById('ingredient_id').value = '';
        }
    }

    function closeRecipeModal() {
        document.getElementById('recipeModal').classList.add('hidden');
    }

    function editRecipe(recipe) {
        showRecipeModal(recipe);
    }

    function deleteRecipe(id) {
        if(confirm('Are you sure you want to delete this recipe?')) {
            window.location.href = 'delete_recipe.php?id=' + id;
        }
    }

    // Similar updates for other JavaScript functions

    // Add these functions after your existing JavaScript code
    function showScheduleModal(schedule = null) {
        document.getElementById('scheduleModal').classList.remove('hidden');
        const form = document.getElementById('scheduleForm');
        form.reset();
        
        if (schedule) {
            // Populate form with schedule data for editing
            document.getElementById('production_id').value = schedule.production_id;
            document.getElementById('order_volume').value = schedule.order_volumn_tbl;
            document.getElementById('capacity').value = schedule.capacity_tbl;
            document.getElementById('staff_availability').value = schedule.staff_availability_tbl;
            document.getElementById('equipment_status').value = schedule.equipment_status_tbl;
        } else {
            // Clear form for new schedule
            document.getElementById('production_id').value = '';
        }
    }

    function closeScheduleModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }

    function editSchedule(schedule) {
        showScheduleModal(schedule);
    }

    function deleteSchedule(id) {
        if(confirm('Are you sure you want to delete this production schedule?')) {
            window.location.href = 'delete_schedule.php?id=' + id;
        }
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        const recipeModal = document.getElementById('recipeModal');
        const scheduleModal = document.getElementById('scheduleModal');
        
        if (event.target === recipeModal) {
            closeRecipeModal();
        }
        if (event.target === scheduleModal) {
            closeScheduleModal();
        }
    }

    function filterBatches() {
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
        const dateFilter = document.getElementById('dateFilter').value;
        const rows = document.querySelectorAll('#batch-section tbody tr');

        rows.forEach(row => {
            const stage = row.children[3].textContent.toLowerCase();
            const date = row.children[1].textContent.split(' ')[0];
            
            const matchesStatus = !statusFilter || stage.includes(statusFilter);
            const matchesDate = !dateFilter || date === dateFilter;
            
            row.style.display = (matchesStatus && matchesDate) ? '' : 'none';
        });
    }
    </script>
</body>
</html> 