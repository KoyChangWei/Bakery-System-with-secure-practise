<?php
session_start();
require_once 'db_connect.php';
echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #f3f4f6; padding: 8px; border-radius: 4px; z-index: 50;">PHP Version: ' . phpversion() . '</div>';
// Check if user is logged in and is a supervisor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'supervisor') {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            overflow-y: auto;
            /* Enable vertical scrolling */
            padding: 1rem;
        }

        .modal-container {
            background-color: white;
            border-radius: 0.5rem;
            max-width: 42rem;
            margin: 2rem auto;
            padding: 1.5rem;
            position: relative;
            min-height: min-content;
        }

        /* Add responsive padding for smaller screens */
        @media (max-width: 640px) {
            .modal-overlay {
                padding: 0.5rem;
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="fixed w-64 h-screen bg-gray-800 text-white">
            <!-- Profile Section -->
            <div class="p-6 border-b border-gray-700">
                <div class="w-20 h-20 rounded-full bg-pink-600 mx-auto mb-4 flex items-center justify-center text-3xl">
                    S
                </div>
                <div class="text-xl text-center">syusyi</div>
                <div class="text-sm text-gray-400 text-center">Supervisor</div>
            </div>

            <!-- Navigation -->
            <nav class="mt-6">
                <button class="nav-item w-full text-left flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"
                    data-tab="recipe-tab" onclick="switchTab(event)">
                    <i class="fas fa-book w-6"></i>
                    <span>Recipe Management</span>
                </button>
                <button class="nav-item w-full text-left flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"
                    data-tab="production-tab" onclick="switchTab(event)">
                    <i class="fas fa-industry w-6"></i>
                    <span>Production Schedule</span>
                </button>
                <button class="nav-item w-full text-left flex items-center px-6 py-3 hover:bg-gray-700 transition-colors"
                    data-tab="batch-tab" onclick="switchTab(event)">
                    <i class="fas fa-tasks w-6"></i>
                    <span>Batch Reports</span>
                </button>
            </nav>

            <!-- Logout Button -->
            <a href="logout.php" class="absolute bottom-0 w-full px-6 py-4 bg-pink-600 hover:bg-pink-700 transition-colors flex items-center">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span>Logout</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="ml-64 flex-1 p-4">
            <!-- Recipe Section -->
            <div id="recipe" class="content-section active">
                <h2 class="text-2xl font-semibold mb-6">Recipe Management</h2>
                <button onclick="showRecipeModal()"
                    class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2 mb-6">
                    <i class="fas fa-plus"></i>
                    Add New Recipe
                </button>

                <!-- Recipe Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">Recipe Name</th>
                                <th class="px-6 py-3 text-left">Ingredients</th>
                                <th class="px-6 py-3 text-left">Preparation Steps</th>
                                <th class="px-6 py-3 text-left">Equipment</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $recipe_sql = "SELECT r.*, 
                                   GROUP_CONCAT(CONCAT(ri.ingredient_name, '|', ri.quantity, '|', ri.unit_tbl) SEPARATOR ';;') as ingredient_data
                            FROM recipe_db r 
                            LEFT JOIN recipe_ingredients ri ON r.recipe_id = ri.recipe_id 
                            GROUP BY r.recipe_id 
                            ORDER BY r.created_at DESC";
                            $stmt = $conn->prepare($recipe_sql);
                            $stmt->execute();
                            $recipe_result = $stmt->get_result();

                            if ($recipe_result->num_rows > 0) {
                                while ($row = $recipe_result->fetch_assoc()) {
                                    $ingredients_html = '';
                                    if ($row['ingredient_data']) {
                                        $ingredients = explode(';;', $row['ingredient_data']);
                                        foreach ($ingredients as $ingredient) {
                                            list($name, $quantity, $unit) = explode('|', $ingredient);
                                            $ingredients_html .= "<div>" . htmlspecialchars($name) . " - " .
                                                htmlspecialchars($quantity) . " " . htmlspecialchars($unit) . "</div>";
                                        }
                                    }
                            ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['recipe_name']); ?></td>
                                        <td class="px-6 py-4"><?php echo $ingredients_html; ?></td>
                                        <td class="px-6 py-4"><?php echo nl2br(htmlspecialchars($row['preparation_step_tbl'])); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['equipment_tbl']); ?></td>
                                        <td class="px-6 py-4">
                                            <div class="flex space-x-2">
                                                <button onclick="editRecipe(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                                    class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteRecipe(<?php echo (int)$row['recipe_id']; ?>)"
                                                    class="text-red-600 hover:text-red-800">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No recipes found</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Production Section -->
            <div id="production" class="content-section">
                <h2 class="text-2xl font-semibold mb-6">Production Schedule</h2>
                <button onclick="showScheduleModal()"
                    class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2 mb-6">
                    <i class="fas fa-plus"></i> Add New Schedule
                </button>

                <!-- Production Schedule Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-800 text-white">
                                <th class="py-3 px-4 text-left">Product</th>
                                <th class="py-3 px-4 text-left">Date</th>
                                <th class="py-3 px-4 text-left">Order Volume</th>
                                <th class="py-3 px-4 text-left">Production Capacity</th>
                                <th class="py-3 px-4 text-left">Staff Assigned</th>
                                <th class="py-3 px-4 text-left">Equipment</th>
                                <th class="py-3 px-4 text-left">Equipment Status</th>
                                <th class="py-3 px-4 text-left">Total Ingredients</th>
                                <th class="py-3 px-4 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, 
                                    r.recipe_name,
                                    e.equipment_name,
                                    e.status as equipment_status,
                                    GROUP_CONCAT(DISTINCT a.name_tbl) as staff_names,
                                    (SELECT GROUP_CONCAT(CONCAT(ri.ingredient_name, ': ', (ri.quantity * p.order_volume), ' ', ri.unit_tbl) SEPARATOR ', ')
                                     FROM recipe_ingredients ri 
                                     WHERE ri.recipe_id = p.recipe_id) as total_ingredients
                                    FROM production_db p
                                    LEFT JOIN recipe_db r ON p.recipe_id = r.recipe_id
                                    LEFT JOIN equipment_status e ON p.equipment_id = e.equipment_id
                                    LEFT JOIN admin_db a ON FIND_IN_SET(a.admin_id, p.staff_availability)
                                    GROUP BY p.production_id
                                    ORDER BY p.production_date DESC";

                            $result = $conn->query($sql);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr class='border-b hover:bg-gray-50'>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['recipe_name']) . "</td>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['production_date']) . "</td>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['order_volume']) . " units</td>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['capacity']) . " units/hour</td>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['staff_names']) . "</td>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['equipment_name']) . "</td>";
                                    echo "<td class='py-2 px-4'>";
                                    echo $row['equipment_status'] == 'Available'
                                        ? "<span class='text-green-600'>Available</span>"
                                        : "<span class='text-blue-600'>Scheduled</span>";
                                    echo "</td>";
                                    echo "<td class='py-2 px-4'>" . htmlspecialchars($row['total_ingredients']) . "</td>";
                                    echo "<td class='py-2 px-4 flex gap-2'>";
                                    echo "<button onclick='editSchedule(" . $row['production_id'] . ")' class='text-blue-600 hover:text-blue-800'><i class='fas fa-edit'></i></button>";
                                    echo "<button onclick='deleteSchedule(" . $row['production_id'] . ")' class='text-red-600 hover:text-red-800'><i class='fas fa-trash'></i></button>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='py-4 px-4 text-center text-gray-500'>No production schedules found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Batch Section -->
            <div id="batch" class="content-section">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Batch Reports</h2>
                    <p class="text-gray-600">View and manage production batch reports</p>
                </div>

                <!-- Batch Reports Table -->
                <div class="bg-white rounded-lg shadow-md overflow-x-auto w-full"> <!-- Added w-full and kept overflow-x-auto -->
                    <table class="w-full whitespace-nowrap"> <!-- Changed to full width and better text wrapping -->
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="w-1/12 px-4 py-3 text-left text-sm font-semibold">Batch No</th>
                                <th class="w-1/12 px-4 py-3 text-left text-sm font-semibold">Start Date</th>
                                <th class="w-1/12 px-4 py-3 text-left text-sm font-semibold">End Date</th>
                                <th class="w-2/12 px-4 py-3 text-left text-sm font-semibold">Workers</th>
                                <th class="w-1/12 px-4 py-3 text-left text-sm font-semibold">Stage</th>
                                <th class="w-3/12 px-4 py-3 text-left text-sm font-semibold">Quality Check</th>
                                <th class="w-2/12 px-4 py-3 text-left text-sm font-semibold">Quantities</th>
                                <th class="w-1/12 px-4 py-3 text-left text-sm font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            try {
                                $batch_sql = "SELECT b.*, 
                                              r.worker_count, 
                                              r.worker_names, 
                                              r.temperature, 
                                              r.moisture, 
                                              r.weight, 
                                              r.target_quantity, 
                                              r.actual_quantity, 
                                              r.defect_count 
                                              FROM batch_db b 
                                              LEFT JOIN batch_reports r ON b.batch_no_tbl = r.batch_no 
                                              ORDER BY b.startDate_tbl DESC";
                                $stmt = $conn->prepare($batch_sql);
                                $stmt->execute();
                                $batch_result = $stmt->get_result();

                                if ($batch_result->num_rows > 0) {
                                    while ($row = $batch_result->fetch_assoc()) {
                                        $status_class = match ($row['status_tbl']) {
                                            'Completed' => 'bg-green-100 text-green-800',
                                            'In Progress' => 'bg-yellow-100 text-yellow-800',
                                            'Scheduled' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };

                                        echo "<tr class='hover:bg-gray-50'>";
                                        echo "<td class='px-4 py-3 text-sm font-medium text-gray-900'>" . htmlspecialchars($row['batch_no_tbl']) . "</td>";
                                        echo "<td class='px-4 py-3 text-sm'>" . date('Y-m-d<\b\r>H:i', strtotime($row['startDate_tbl'])) . "</td>";
                                        echo "<td class='px-4 py-3 text-sm'>" . ($row['endDate_tbl'] ? date('Y-m-d<\b\r>H:i', strtotime($row['endDate_tbl'])) : '-') . "</td>";

                                        echo "<td class='px-4 py-3'>
                                            <div class='text-sm space-y-1'>
                                                <div class='font-medium'>Count: " . htmlspecialchars($row['worker_count']) . "</div>
                                                <div class='text-gray-600'>Names: " . htmlspecialchars($row['worker_names']) . "</div>
                                            </div>
                                        </td>";

                                        echo "<td class='px-4 py-3 text-sm'>" . ucwords(htmlspecialchars($row['production_stage_tbl'])) . "</td>";

                                        echo "<td class='px-4 py-3'>
                                            <div class='text-sm space-y-1'>
                                                <div class='grid grid-cols-2 gap-2'>
                                                    <div>Temp: " . htmlspecialchars($row['temperature']) . "°C</div>
                                                    <div>Moisture: " . htmlspecialchars($row['moisture']) . "%</div>
                                                    <div>Weight: " . htmlspecialchars($row['weight']) . "g</div>
                                                </div>
                                                <div class='text-gray-600'>Notes: " . nl2br(htmlspecialchars($row['quality_check_tbl'])) . "</div>
                                            </div>
                                        </td>";

                                        echo "<td class='px-4 py-3'>
                                            <div class='text-sm space-y-1'>
                                                <div class='text-green-600'>Target: " . htmlspecialchars($row['target_quantity']) . "</div>
                                                <div class='text-blue-600'>Actual: " . htmlspecialchars($row['actual_quantity']) . "</div>
                                                <div class='text-red-600'>Defects: " . htmlspecialchars($row['defect_count']) . "</div>
                                            </div>
                                        </td>";

                                        echo "<td class='px-4 py-3'>
                                                <span class='inline-flex px-2 py-1 rounded-full text-xs font-medium {$status_class}'>
                                                    {$row['status_tbl']}
                                                </span>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='px-4 py-3 text-center text-gray-500'>No batch records found</td></tr>";
                                }

                                $stmt->close();
                            } catch (Exception $e) {
                                echo "<tr><td colspan='8' class='px-4 py-3 text-center text-red-500'>Error loading batch records: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recipe Modal -->
        <div id="recipeModal" class="modal-overlay hidden">
            <div class="modal-container">

                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Add/Edit Recipe</h3>
                    <button onclick="closeRecipeModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="recipeForm" action="process_recipe.php" method="POST" class="space-y-4">
                    <input type="hidden" id="recipe_id" name="recipe_id">

                    <!-- Recipe Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipe Name</label>
                        <input type="text" id="recipe_name" name="recipe_name" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>

                    <!-- Ingredients Section -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ingredients</label>
                        <div id="ingredients-container" class="space-y-2"></div>
                        <button type="button" onclick="addIngredientRow()"
                            class="mt-2 text-pink-600 hover:text-pink-700 flex items-center gap-1">
                            <i class="fas fa-plus"></i> Add Ingredient
                        </button>
                    </div>

                    <!-- Preparation Steps -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Preparation Steps</label>
                        <div id="steps-container" class="space-y-2"></div>
                        <button type="button" onclick="addStep()"
                            class="mt-2 text-pink-600 hover:text-pink-700 flex items-center gap-1">
                            <i class="fas fa-plus"></i> Add Step
                        </button>
                    </div>

                    <!-- Equipment Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
                        <select id="equipment" name="equipment_tbl" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="">Select Equipment</option>
                            <?php
                            $equipment_sql = "
                                                SELECT 
                                                    es.equipment_name,
                                                    CASE 
                                                        WHEN EXISTS (
                                                            SELECT 1 FROM recipe_db r
                                                            WHERE FIND_IN_SET(es.equipment_name, r.equipment_tbl)
                                                        ) THEN 'In Use'
                                                        ELSE es.status 
                                                    END AS current_status
                                                FROM equipment_status es
                                                ORDER BY es.equipment_name";

                            $stmt = $conn->prepare($equipment_sql);
                            $stmt->execute();
                            $equipment_result = $stmt->get_result();

                            while ($equip = $equipment_result->fetch_assoc()) {
                                $status = $equip['current_status'] !== 'Available' ? " ({$equip['current_status']})" : '';
                                $disabled = $equip['current_status'] !== 'Available' ? 'disabled' : '';
                                echo "<option value='" . htmlspecialchars($equip['equipment_name']) . "' $disabled>"
                                    . htmlspecialchars($equip['equipment_name']) . $status . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeRecipeModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
                            Save Recipe
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Schedule Modal -->
        <div id="scheduleModal" class="modal-overlay hidden">
            <div class="modal-container">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold">Add/Edit Production Schedule</h3>
                    <button onclick="closeScheduleModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="scheduleForm" action="process_schedule.php" method="POST" class="space-y-4">
                    <input type="hidden" id="schedule_id" name="schedule_id">

                    <!-- Recipe Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Recipe</label>
                        <select id="product" name="product" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="">Select Recipe</option>
                            <?php
                            $recipe_sql = "SELECT recipe_id, recipe_name FROM recipe_db";
                            $recipe_result = $conn->query($recipe_sql);
                            while ($recipe = $recipe_result->fetch_assoc()) {
                                $selected = ($schedule && $schedule['recipe_id'] == $recipe['recipe_id']) ? 'selected' : '';
                                echo "<option value='{$recipe['recipe_id']}' $selected>{$recipe['recipe_name']}</option>";
                            }
                            ?>
                        </select>


                    </div>

                    <!-- Production Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Production Date</label>
                        <input type="date" id="production_date" name="production_date" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>

                    <!-- Order Volume -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Order Volume</label>
                        <input type="number" id="order_volume" name="order_volume" required min="1"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>

                    <!-- Add Production Capacity field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Production Capacity (units/hour)</label>
                        <input type="number" id="capacity" name="capacity" required min="1"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>

                    <!-- Equipment Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
                        <select id="equipment_id" name="equipment_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="">Select Equipment</option>
                            <?php
                            $equipment_sql = "SELECT equipment_id, equipment_name FROM equipment_status WHERE status = 'Available'";
                            $equipment_result = $conn->query($equipment_sql);
                            while ($equipment = $equipment_result->fetch_assoc()) {
                                $selected = ($schedule && $schedule['equipment_id'] == $equipment['equipment_id']) ? 'selected' : '';
                                echo "<option value='{$equipment['equipment_id']}' $selected>{$equipment['equipment_name']}</option>";
                            }
                            ?>
                        </select>

                    </div>

                    <!-- Staff Assignment -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Staff</label>
                        <div id="staff-container" class="space-y-2">
                            <!-- Staff select elements will be added here -->
                        </div>
                        <button type="button" onclick="addStaff()"
                            class="mt-2 text-pink-600 hover:text-pink-700 flex items-center gap-1">
                            <i class="fas fa-plus"></i> Add Staff
                        </button>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button"
                            onclick="closeScheduleModal()"
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

        <script>
            // Tab switching functionality
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tab system
                function initializeTabs() {
                    // Get all tab buttons and content sections
                    const tabButtons = document.querySelectorAll('.nav-item');
                    const contentSections = document.querySelectorAll('.content-section');

                    // Hide all sections except the first one
                    contentSections.forEach((section, index) => {
                        if (index === 0) {
                            section.style.display = 'block';
                            section.classList.add('active');
                        } else {
                            section.style.display = 'none';
                            section.classList.remove('active');
                        }
                    });

                    // Set first tab as active
                    if (tabButtons.length > 0) {
                        tabButtons[0].classList.add('bg-gray-700');
                    }
                }

                // Tab switching function
                window.switchTab = function(event) {
                    if (!event || !event.currentTarget) return;

                    // Get the tab name from the button's data-tab attribute
                    const tabName = event.currentTarget.getAttribute('data-tab');
                    if (!tabName) return;

                    // Get the section name by removing '-tab' suffix
                    const sectionName = tabName.replace('-tab', '');
                    
                    try {
                        // Get all tab buttons and content sections
                        const tabButtons = document.querySelectorAll('.nav-item');
                        const contentSections = document.querySelectorAll('.content-section');

                        // First, hide all sections and remove active classes
                        contentSections.forEach(section => {
                            section.style.display = 'none';
                            section.classList.remove('active');
                        });

                        tabButtons.forEach(button => {
                            button.classList.remove('bg-gray-700');
                        });

                        // Show the selected section
                        const selectedSection = document.getElementById(sectionName);
                        if (!selectedSection) {
                            console.error(`Section with id "${sectionName}" not found`);
                            return;
                        }

                        // Show the selected section and add active class
                        selectedSection.style.display = 'block';
                        selectedSection.classList.add('active');
                        event.currentTarget.classList.add('bg-gray-700');

                    } catch (error) {
                        console.error('Error switching tabs:', error);
                    }
                };

                // Initialize tabs on page load
                initializeTabs();

                // Set up click handlers for all tab buttons
                document.querySelectorAll('.nav-item').forEach(button => {
                    button.addEventListener('click', switchTab);
                });
            });

            function editSchedule(scheduleId) {
                fetch(`get_schedule.php?id=${scheduleId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Schedule data:', data);
                        if (data.success && data.data) {
                            showScheduleModal(data.data);
                        } else {
                            throw new Error(data.message || "Failed to load schedule data");
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching schedule data:", error);
                        alert("Failed to load schedule data: " + error.message);
                    });
            }

            function showScheduleModal(schedule = null) {
                console.log('Showing schedule modal with data:', schedule);

                const modal = document.getElementById('scheduleModal');
                const staffContainer = document.getElementById('staff-container');
                const form = document.getElementById('scheduleForm');

                if (!modal || !form) return;

                // Reset form and show modal
                form.reset();
                modal.classList.remove('hidden');
                staffContainer.innerHTML = '';

                if (schedule) {
                    try {
                        // Set basic fields
                        document.getElementById('schedule_id').value = schedule.production_id || '';
                        document.getElementById('product').value = schedule.recipe_id || '';
                        document.getElementById('production_date').value = schedule.production_date || '';
                        document.getElementById('order_volume').value = schedule.order_volume || '';
                        document.getElementById('capacity').value = schedule.capacity || '';

                        // Handle equipment selection
                        const equipmentSelect = document.getElementById('equipment_id');
                        if (equipmentSelect && schedule.equipment_id) {
                            // First, add the current equipment as an option
                            equipmentSelect.innerHTML = '<option value="">Select Equipment</option>';
                            const currentOption = new Option(schedule.equipment_name, schedule.equipment_id, true, true);
                            equipmentSelect.add(currentOption);
                            equipmentSelect.value = schedule.equipment_id;

                            // Then fetch available equipment
                            fetch(`get_available_equipment.php?production_date=${encodeURIComponent(schedule.production_date)}&exclude_id=${schedule.equipment_id}&recipe_id=${schedule.recipe_id}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success && data.data) {
                                        data.data.forEach(equip => {
                                            if (equip.equipment_id !== schedule.equipment_id) {
                                                equipmentSelect.add(new Option(equip.equipment_name, equip.equipment_id));
                                            }
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error loading equipment:', error);
                                });
                        }

                        // Handle staff assignments
                        if (schedule.staff_availability && schedule.staff_names) {
                            const staffIds = schedule.staff_availability ? schedule.staff_availability.toString().split(',').map(id => id.trim()) : [];
                            const staffNames = schedule.staff_names ? schedule.staff_names.toString().split(',').map(name => name.trim()) : [];

                            if (staffIds.length > 0) {
                                staffIds.forEach((staffId, index) => {
                                    if (!staffId) return;

                                    const div = document.createElement('div');
                                    div.className = 'flex gap-2 items-center mb-2';

                                    const select = document.createElement('select');
                                    select.name = 'staff[]';
                                    select.required = true;
                                    select.className = 'flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500';

                                    // Add current staff member as first option
                                    select.innerHTML = '<option value="">Select Staff</option>';
                                    if (staffId && staffNames[index]) {
                                        const currentStaff = new Option(staffNames[index], staffId, true, true);
                                        select.add(currentStaff);
                                        select.value = staffId;
                                    }

                                    div.appendChild(select);

                                    // Add remove button if not first staff member
                                    if (index > 0) {
                                        const removeButton = document.createElement('button');
                                        removeButton.type = 'button';
                                        removeButton.className = 'text-red-500 hover:text-red-700';
                                        removeButton.innerHTML = '<i class="fas fa-times"></i>';
                                        removeButton.onclick = () => div.remove();
                                        div.appendChild(removeButton);
                                    }

                                    staffContainer.appendChild(div);

                                    // Fetch available staff
                                    const excludeIds = staffIds.filter(id => id !== staffId);
                                    fetch(`get_available_staff.php?production_date=${encodeURIComponent(schedule.production_date)}&exclude_ids=${excludeIds.join(',')}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success && Array.isArray(data.data)) {
                                                data.data.forEach(staff => {
                                                    if (staff.admin_id !== staffId) {
                                                        select.add(new Option(staff.name_tbl, staff.admin_id));
                                                    }
                                                });
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Staff loading error:', error);
                                        });
                                });
                            } else {
                                addStaff(); // Add one empty staff select if no staff assigned
                            }
                        } else {
                            addStaff(); // Add one empty staff select if no staff data
                        }

                    } catch (error) {
                        console.error('Error setting form values:', error);
                        // Don't show alert, just log the error and continue
                        console.log('Continuing with form display despite error:', error.message);
                    }
                } else {
                    addStaff(); // Add one empty staff select for new schedule
                }
            }

            // Function to delete schedule
            function deleteSchedule(scheduleId) {
                if (confirm('Are you sure you want to delete this schedule?')) {
                    fetch(`delete_schedule.php?schedule_id=${scheduleId}`, {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Schedule deleted successfully');
                                window.location.reload();
                            } else {
                                alert('Failed to delete schedule');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to delete schedule');
                        });
                }
            }

            // Simplified equipment handling
            async function checkEquipmentAvailability(selectedEquipment = null, recipeId = null) {
                const equipmentSelect = document.getElementById('equipment');

                try {
                    // First try to use the existing options if they exist
                    if (equipmentSelect.options.length > 1) {
                        if (selectedEquipment) {
                            equipmentSelect.value = selectedEquipment;
                        }
                        return;
                    }

                    // Only fetch from server if needed
                    const response = await fetch('check_equipment.php');
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();

                    // Clear and repopulate the dropdown
                    equipmentSelect.innerHTML = '<option value="">Select Equipment</option>';

                    data.forEach(equip => {
                        const status = equip.current_status !== 'Available' ? ` (${equip.current_status})` : '';
                        const option = new Option(`${equip.equipment_name}${status}`, equip.equipment_name);
                        option.disabled = equip.current_status !== 'Available' && equip.equipment_name !== selectedEquipment;
                        option.selected = equip.equipment_name === selectedEquipment;
                        equipmentSelect.add(option);
                    });
                } catch (error) {
                    console.log('Using fallback equipment options');
                    // If fetch fails, use the existing PHP-generated options
                    if (selectedEquipment) {
                        equipmentSelect.value = selectedEquipment;
                    }
                }
            }

            // Update showRecipeModal function to handle equipment properly
            function showRecipeModal(recipe = null) {
                const modal = document.getElementById('recipeModal');
                const form = document.getElementById('recipeForm');

                if (!modal || !form) return;

                // Reset form and show modal
                form.reset();
                modal.classList.remove('hidden');

                // Clear containers
                document.getElementById('ingredients-container').innerHTML = '';
                document.getElementById('steps-container').innerHTML = '';

                if (recipe) {
                    // Populate fields with sanitized data
                    document.getElementById('recipe_id').value = recipe.recipe_id || '';
                    document.getElementById('recipe_name').value = recipe.recipe_name || '';

                    // Set equipment - allow selecting current equipment even if in use
                    const equipmentSelect = document.getElementById('equipment');
                    if (equipmentSelect && recipe.equipment_tbl) {
                        // First, ensure the current equipment is in the options list
                        let currentEquipmentExists = false;
                        Array.from(equipmentSelect.options).forEach(option => {
                            if (option.value === recipe.equipment_tbl) {
                                currentEquipmentExists = true;
                                option.selected = true;
                                option.disabled = false; // Enable the current equipment
                            }
                        });

                        // If current equipment isn't in the list, add it
                        if (!currentEquipmentExists) {
                            const option = new Option(recipe.equipment_tbl, recipe.equipment_tbl, true, true);
                            option.disabled = false;
                            equipmentSelect.add(option);
                        }
                    }

                    // Handle ingredients
                    if (recipe.ingredients && recipe.ingredients.length > 0) {
                        recipe.ingredients.forEach(ingredient => {
                            addIngredientRowWithValues(
                                ingredient.name,
                                ingredient.quantity,
                                ingredient.unit
                            );
                        });
                    } else {
                        addIngredientRow();
                    }

                    // Handle preparation steps
                    if (recipe.preparation_step_tbl) {
                        // Split steps and remove any numbering
                        const steps = recipe.preparation_step_tbl
                            .split('\n')
                            .map(step => step.trim())
                            .map(step => step.replace(/^\d+\.\s*/, '')) // Remove any existing numbering
                            .filter(step => step); // Remove empty steps

                        if (steps.length > 0) {
                            steps.forEach((step, index) => {
                                if (index === 0) {
                                    // For first step, create the row directly in container
                                    document.getElementById('steps-container').innerHTML = createStepRow(step, 1);
                                } else {
                                    // For subsequent steps, add new rows
                                    addStepWithValue(step);
                                }
                            });
                        }
                    } else {
                        addStep();
                    }
                } else {
                    // Add empty rows for new recipe
                    addIngredientRow();
                    addStep();
                }

                // Re-enable the current equipment in the dropdown
                const equipmentSelect = document.getElementById('equipment');
                const recipeId = document.getElementById('recipe_id').value;
                if (equipmentSelect && recipeId) {
                    Array.from(equipmentSelect.options).forEach(option => {
                        // Enable the current equipment for this recipe
                        if (recipe && option.value === recipe.equipment_tbl) {
                            option.disabled = false;
                        }
                    });
                }
            }

            function createStepRow(step = '', number = 1) {
                const sanitizedStep = sanitizeInput(step);
                return `
                    <div class="step-row flex items-center gap-2">
                        <span class="text-gray-600">${number}.</span>
                        <textarea 
                            name="preparation_steps[]" 
                            placeholder="Enter step description" 
                            required
                            maxlength="1000"
                            class="flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500 resize-y min-h-[60px]"
                            oninput="validateStep(this)"
                        >${sanitizedStep}</textarea>
                        <button type="button" 
                            onclick="removeStep(this)"
                            class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }

            // Enhanced input sanitization function
            function sanitizeInput(input) {
                if (!input) return '';

                // Convert to string and trim
                input = input.toString().trim();

                // Check for XSS patterns
                const xssPatterns = [
                    /<script[^>]*>.*?<\/script>/i,
                    /<\s*script/i,
                    /<\s*\/\s*script/i,
                    /javascript:/i,
                    /onclick/i,
                    /onload/i,
                    /onerror/i,
                    /onmouseover/i,
                    /onmouseout/i,
                    /onkeypress/i,
                    /onkeydown/i,
                    /onkeyup/i,
                    /onfocus/i,
                    /onblur/i,
                    /onsubmit/i,
                    /onreset/i,
                    /onselect/i,
                    /onchange/i,
                    /<img[^>]+src[^>]*>/i,
                    /data:/i,
                    /alert\s*\(/i,
                    /eval\s*\(/i,
                    /Function\s*\(/i,
                    /setTimeout\s*\(/i,
                    /setInterval\s*\(/i,
                    /document\./i,
                    /window\./i,
                    /<iframe/i,
                    /<embed/i,
                    /<object/i,
                    /<applet/i,
                    /<meta/i,
                    /<svg/i,
                    /<math/i,
                    /<form/i,
                    /base64/i,
                    /&#/i,
                    /\\u/i,
                    /\\\x/i
                ];

                // Check for XSS patterns
                for (const pattern of xssPatterns) {
                    if (pattern.test(input)) {
                        throw new Error('Potential XSS attack detected');
                    }
                }

                // Remove HTML tags
                input = input.replace(/<[^>]*>/g, '');

                // Remove dangerous characters
                input = input.replace(/[<>'"`;{}()\[\]\\&|]/g, '');

                // Remove control characters
                input = input.replace(/[\x00-\x1F\x7F-\x9F]/g, '');

                // Remove multiple spaces
                input = input.replace(/\s+/g, ' ');

                return input.trim();
            }

            // Enhanced recipe name validation
            function validateRecipeName(input) {
                try {
                    const value = input.value.trim().toLowerCase(); // Convert to lowercase for case-insensitive checking

                    // Check for empty value
                    if (!value) {
                        input.setCustomValidity('Recipe name cannot be empty');
                        input.reportValidity();
                        return;
                    }

                    // Suspicious words and patterns to check (including concatenated forms)
                    const suspiciousPatterns = [
                        'script',
                        'alert',
                        'xss',
                        'javascript',
                        'eval',
                        'onload',
                        'onerror',
                        'onclick',
                        'onmouseover',
                        'onmouseout',
                        'document',
                        'window',
                        'console',
                        'fetch',
                        'ajax',
                        'cookie',
                        'localStorage',
                        'sessionStorage',
                        'function',
                        'Promise',
                        'async',
                        'await',
                        'iframe',
                        'object',
                        'embed',
                        'applet'
                    ];

                    // Remove spaces and check for concatenated suspicious words
                    const noSpaceValue = value.replace(/\s+/g, '');
                    for (const pattern of suspiciousPatterns) {
                        if (noSpaceValue.includes(pattern)) {
                            input.setCustomValidity('Invalid recipe name: contains suspicious terms');
                            input.reportValidity();
                            return;
                        }
                    }

                    // Check for XSS patterns
                    const xssPatterns = [
                        /<[^>]*>/g,  // Any HTML tags
                        /&#[x]?\d+;?/g,  // HTML entities
                        /\\x[0-9a-f]+/gi,  // Hex escape sequences
                        /\\u[0-9a-f]+/gi,  // Unicode escape sequences
                        /\(\)/g,  // Empty parentheses
                        /\[\]/g,  // Empty brackets
                        /\{\}/g,  // Empty curly braces
                        /;/g,  // Semicolons
                        /:/g,  // Colons
                        /\$/g,  // Dollar signs
                        /=/g,  // Equals signs
                        /\+/g,  // Plus signs
                        /\^/g,  // Carets
                        /~/g,  // Tildes
                        /`/g,  // Backticks
                        /'/g,  // Single quotes
                        /"/g,  // Double quotes
                        /!/g,  // Exclamation marks
                        /@/g,  // At signs
                        /#/g,  // Hash signs
                        /%/g,  // Percent signs
                        /\*/g,  // Asterisks
                        /\?/g,  // Question marks
                        /\|/g,  // Vertical bars
                        /\\/g,  // Backslashes
                        /\//g   // Forward slashes
                    ];

                    for (const pattern of xssPatterns) {
                        if (pattern.test(value)) {
                            input.setCustomValidity('Recipe name contains invalid characters');
                            input.reportValidity();
                            return;
                        }
                    }

                    // Only allow letters, numbers, spaces, and specific punctuation
                    if (!/^[A-Za-z0-9\s\-.,()]{1,100}$/.test(input.value.trim())) {
                        input.setCustomValidity('Recipe name can only contain letters, numbers, spaces, and basic punctuation (.,-)()');
                        input.reportValidity();
                        return;
                    }

                    // Check length
                    if (value.length > 100) {
                        input.setCustomValidity('Recipe name is too long (maximum 100 characters)');
                        input.reportValidity();
                        return;
                    }

                    // If all checks pass, clear any previous validation messages
                    input.setCustomValidity('');
                } catch (error) {
                    console.error('Recipe name validation error:', error);
                    input.setCustomValidity('An error occurred during validation');
                    input.reportValidity();
                }
            }

            // Add event listener for recipe name input
            document.addEventListener('DOMContentLoaded', function() {
                const recipeNameInput = document.querySelector('[name="recipe_name"]');
                if (recipeNameInput) {
                    recipeNameInput.addEventListener('input', function() {
                        validateRecipeName(this);
                    });

                    // Also validate on form submission
                    recipeNameInput.closest('form').addEventListener('submit', function(e) {
                        validateRecipeName(recipeNameInput);
                        if (!recipeNameInput.validity.valid) {
                            e.preventDefault();
                        }
                    });
                }
            });

            // Enhanced step validation
            function validateStep(textarea) {
                try {
                    const value = textarea.value.trim();

                    // Check for empty value
                    if (!value) {
                        textarea.setCustomValidity('Preparation step cannot be empty');
                        textarea.reportValidity();
                        return;
                    }

                    // Check for XSS patterns first
                    const sanitizedValue = sanitizeInput(value);
                    if (sanitizedValue !== value) {
                        textarea.setCustomValidity('Invalid characters detected in step');
                        textarea.reportValidity();
                        textarea.value = sanitizedValue;
                        return;
                    }

                    // Check length
                    if (value.length > 500) {
                        textarea.setCustomValidity('Step is too long (maximum 500 characters)');
                        textarea.reportValidity();
                        return;
                    }

                    // Only allow letters, numbers, spaces, and basic punctuation
                    if (!/^[A-Za-z0-9\s\-.,()°F℃]{1,500}$/.test(value)) {
                        textarea.setCustomValidity('Step can only contain letters, numbers, spaces, basic punctuation (.,-)(), and temperature symbols');
                        textarea.reportValidity();
                        return;
                    }

                    textarea.setCustomValidity('');
                } catch (error) {
                    textarea.setCustomValidity(error.message);
                    textarea.reportValidity();
                }
            }

            // Add event listeners for real-time validation
            document.addEventListener('DOMContentLoaded', function() {
                // Recipe name validation
                const recipeNameInput = document.querySelector('[name="recipe_name"]');
                if (recipeNameInput) {
                    recipeNameInput.addEventListener('input', function() {
                        validateRecipeName(this);
                    });
                }

                // Preparation steps validation
                document.getElementById('steps-container').addEventListener('input', function(e) {
                    if (e.target.matches('[name="preparation_steps[]"]')) {
                        validateStep(e.target);
                    }
                });

                // Ingredient validation
                const ingredientsContainer = document.getElementById('ingredients-container');
                if (ingredientsContainer) {
                    ingredientsContainer.addEventListener('input', function(e) {
                        if (e.target.matches('[name="ingredient_name[]"]')) {
                            validateIngredientName(e.target);
                        }
                    });
                }
            });

            // Update form submission validation
            document.getElementById('recipeForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                try {
                    const formData = new FormData(this);

                    // Validate recipe name
                    const recipeName = formData.get('recipe_name');
                    if (!recipeName || !/^[A-Za-z0-9\s\-.,]*$/.test(recipeName)) {
                        throw new Error('Invalid recipe name - only letters, numbers, spaces, and basic punctuation (.,-)');
                    }

                    // Validate ingredients
                    const ingredients = document.querySelectorAll('#ingredients-container .ingredient-row');
                    if (ingredients.length === 0) {
                        throw new Error('At least one ingredient is required');
                    }

                    for (let i = 0; i < ingredients.length; i++) {
                        const name = ingredients[i].querySelector('[name="ingredient_name[]"]').value;
                        const quantity = ingredients[i].querySelector('[name="quantity[]"]').value;
                        const unit = ingredients[i].querySelector('[name="unit[]"]').value;

                        if (!name || !quantity || !unit) {
                            throw new Error(`Please fill in all fields for ingredient at position ${i + 1}`);
                        }

                        // Validate ingredient name
                        if (!/^[A-Za-z0-9\s\-.,]*$/.test(name)) {
                            throw new Error(`Invalid ingredient name at position ${i + 1} - only letters, numbers, and basic punctuation allowed`);
                        }

                        // Validate quantity
                        const qtyNum = parseFloat(quantity);
                        if (isNaN(qtyNum) || qtyNum <= 0 || qtyNum > 1000000) {
                            throw new Error(`Invalid quantity at position ${i + 1} - must be between 0 and 1,000,000`);
                        }

                        // Validate unit
                        if (!['g', 'kg', 'ml', 'L', 'pcs', 'cups', 'tbsp', 'tsp'].includes(unit)) {
                            throw new Error(`Invalid unit at position ${i + 1}`);
                        }
                    }

                    // Validate preparation steps
                    const steps = document.querySelectorAll('#steps-container .step-row textarea');
                    if (steps.length === 0) {
                        throw new Error('At least one preparation step is required');
                    }

                    for (let i = 0; i < steps.length; i++) {
                        const step = steps[i].value;
                        if (!step) {
                            throw new Error(`Step ${i + 1} cannot be empty`);
                        }

                        // Check for dangerous characters
                        if (!/^[A-Za-z0-9\s\-.,]*$/.test(step)) {
                            throw new Error(`Invalid characters in step ${i + 1} - only letters, numbers, and basic punctuation allowed`);
                        }

                        if (step.length > 1000) {
                            throw new Error(`Step ${i + 1} is too long (maximum 1000 characters)`);
                        }
                    }

                    // Validate equipment
                    const equipment = formData.get('equipment_tbl');
                    if (!equipment) {
                        throw new Error('Please select equipment');
                    }

                    // Submit form
                    const response = await fetch('process_recipe.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (!result) {
                        throw new Error('Invalid response from server');
                    }

                    if (!result.success) {
                        throw new Error(result.message || result.error || 'Failed to save recipe');
                    }

                    // Success - reload page
                    window.location.reload();
                } catch (error) {
                    console.error('Form submission error:', error);
                    alert(error.message || 'Failed to save recipe. Please try again.');
                }
            });

            // Add input validation for recipe name field
            document.querySelector('[name="recipe_name"]').addEventListener('input', function(e) {
                try {
                    const validPattern = /^[A-Za-z0-9\s\-.,]*$/;
                    if (!validPattern.test(this.value)) {
                        this.setCustomValidity('Only letters, numbers, spaces, and basic punctuation (.,-)');
                        this.reportValidity();
                        this.value = this.value.replace(/[^A-Za-z0-9\s\-.,]/g, '');
                    } else {
                        this.setCustomValidity('');
                    }
                } catch (error) {
                    this.setCustomValidity(error.message);
                    this.reportValidity();
                }
            });

            // Add input validation for ingredient names
            document.getElementById('ingredients-container').addEventListener('input', function(e) {
                if (e.target.matches('[name="ingredient_name[]"]')) {
                    validateIngredientName(e.target);
                }
            });

            // Update preparation steps validation
            document.getElementById('steps-container').addEventListener('input', function(e) {
                if (e.target.matches('[name="preparation_steps[]"]')) {
                    validateStep(e.target);
                }
            });

            // Close modal function
            function closeRecipeModal() {
                const modal = document.getElementById('recipeModal');
                if (modal) {
                    modal.classList.add('hidden');
                    // Reset form
                    const form = document.getElementById('recipeForm');
                    if (form) {
                        form.reset();
                    }
                }
            }

            // Initialize when document loads
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Initializing schedule functionality');

                // Add click handler for Add Schedule button
                const addBtn = document.getElementById('addScheduleBtn');
                if (addBtn) {
                    addBtn.onclick = function(e) {
                        e.preventDefault();
                        showScheduleModal();
                    };
                }
                // Add submit handler for schedule form
                const scheduleForm = document.getElementById('scheduleForm');
                if (scheduleForm) {
                    scheduleForm.addEventListener('submit', async function(e) {
                        e.preventDefault();
                        try {
                            const formData = new FormData(this);
                            const scheduleId = document.getElementById('schedule_id').value;

                            // Determine which endpoint to use based on whether we're editing or creating
                            const endpoint = scheduleId ? 'update_schedule.php' : 'process_schedule.php';

                            const response = await fetch(endpoint, {
                                method: 'POST',
                                body: formData
                            });

                            const result = await response.json();
                            if (result.success) {
                                alert(scheduleId ? 'Schedule updated successfully!' : 'Schedule created successfully!');
                                window.location.reload();
                            } else {
                                throw new Error(result.message || 'Failed to save schedule');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Failed to save schedule: ' + error.message);
                        }
                    });
                }

                // Add change handler for production date
                const productionDate = document.getElementById('production_date');
                if (productionDate) {
                    productionDate.addEventListener('change', function() {
                        const staffContainer = document.getElementById('staff-container');
                        if (staffContainer) {
                            staffContainer.innerHTML = '';
                            if (this.value) {
                                const select = createStaffSelect();
                                staffContainer.appendChild(select);
                            }
                        }
                    });
                }
            });

            function createStaffSelect() {
                const div = document.createElement('div');
                div.className = 'flex gap-2 items-center mb-2';

                const select = document.createElement('select');
                select.name = 'staff[]';
                select.required = true;
                select.className = 'flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500';

                // Get selected date
                const productionDate = document.getElementById('production_date')?.value;
                if (!productionDate) {
                    select.innerHTML = '<option value="">Select production date first</option>';
                    div.appendChild(select);
                    return div;
                }

                // Keep track of the current selection
                const currentValue = select.value;

                fetch(`get_available_staff.php?production_date=${encodeURIComponent(productionDate)}`)
                    .then(response => response.json())
                    .then(data => {
                        select.innerHTML = '<option value="">Select Staff</option>';
                        if (data.success && Array.isArray(data.data)) {
                            data.data.forEach(staff => {
                                const option = new Option(staff.name_tbl, staff.admin_id);
                                select.add(option);
                            });
                            // Restore the previous selection if it exists
                            if (currentValue) {
                                select.value = currentValue;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Staff loading error:', error);
                        select.innerHTML = '<option value="">Error loading staff</option>';
                    });

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'text-red-500 hover:text-red-700';
                removeButton.innerHTML = '<i class="fas fa-times"></i>';
                removeButton.onclick = () => {
                    if (document.getElementById('staff-container').children.length > 1) {
                        div.remove();
                    }
                };

                div.appendChild(select);
                if (document.getElementById('staff-container').children.length > 0) {
                    div.appendChild(removeButton);
                }

                return div;
            }

            // Update addStaff function to check maximum staff limit
            function addStaff() {
                const container = document.getElementById('staff-container');
                if (!container) return;

                const productionDate = document.getElementById('production_date')?.value;
                if (!productionDate) {
                    alert('Please select a production date first');
                    return;
                }

                // Check maximum staff limit
                if (container.children.length >= 3) {
                    alert('Maximum 3 staff members can be assigned');
                    return;
                }

                // Get currently selected staff IDs to exclude them
                const selectedStaffIds = Array.from(container.querySelectorAll('select'))
                    .map(select => select.value)
                    .filter(value => value);

                // Create new staff select container
                const div = document.createElement('div');
                div.className = 'flex gap-2 items-center mb-2';

                const select = document.createElement('select');
                select.name = 'staff[]';
                select.required = true;
                select.className = 'flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500';
                select.innerHTML = '<option value="">Loading staff...</option>';

                // Add remove button
                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'text-red-500 hover:text-red-700';
                removeButton.innerHTML = '<i class="fas fa-times"></i>';
                removeButton.onclick = () => div.remove();

                div.appendChild(select);
                div.appendChild(removeButton);
                container.appendChild(div);

                // Fetch available staff excluding already selected ones
                fetch(`get_available_staff.php?production_date=${encodeURIComponent(productionDate)}&exclude_ids=${selectedStaffIds.join(',')}`)
                    .then(response => response.json())
                    .then(data => {
                        select.innerHTML = '<option value="">Select Staff</option>';
                        if (data.success && Array.isArray(data.data)) {
                            data.data.forEach(staff => {
                                if (!selectedStaffIds.includes(staff.admin_id)) {
                                    select.add(new Option(staff.name_tbl, staff.admin_id));
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Staff loading error:', error);
                        select.innerHTML = '<option value="">Error loading staff</option>';
                    });
            }

            // Ingredient Management Functions
            function createIngredientRow(name = '', quantity = '', unit = '') {
                const sanitizedName = sanitizeInput(name);
                const sanitizedQuantity = parseFloat(quantity) || '';
                const sanitizedUnit = sanitizeInput(unit);

                return `
                    <div class="ingredient-row flex gap-2 items-center mb-2">
                        <input type="text" 
                            name="ingredient_name[]" 
                            value="${sanitizedName}" 
                            placeholder="Ingredient Name" 
                            required
                            maxlength="50"
                            class="flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500"
                            oninput="validateIngredientName(this)">
                        <input type="number" 
                            name="quantity[]" 
                            value="${sanitizedQuantity}" 
                            placeholder="Qty" 
                            required
                            min="0.01"
                            max="10000"
                            step="0.01"
                            class="w-24 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                        <select name="unit[]" 
                            required
                            class="w-32 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="" ${!sanitizedUnit ? 'selected' : ''}>Unit</option>
                            <option value="g" ${sanitizedUnit === 'g' ? 'selected' : ''}>Grams (g)</option>
                            <option value="kg" ${sanitizedUnit === 'kg' ? 'selected' : ''}>Kilograms (kg)</option>
                            <option value="ml" ${sanitizedUnit === 'ml' ? 'selected' : ''}>Milliliters (ml)</option>
                            <option value="L" ${sanitizedUnit === 'L' ? 'selected' : ''}>Liters (L)</option>
                            <option value="pcs" ${sanitizedUnit === 'pcs' ? 'selected' : ''}>Pieces</option>
                            <option value="cups" ${sanitizedUnit === 'cups' ? 'selected' : ''}>Cups</option>
                            <option value="tbsp" ${sanitizedUnit === 'tbsp' ? 'selected' : ''}>Tablespoons</option>
                            <option value="tsp" ${sanitizedUnit === 'tsp' ? 'selected' : ''}>Teaspoons</option>
                        </select>
                        <button type="button" 
                            onclick="removeIngredientRow(this)" 
                            class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }

            function addIngredientRow() {
                const container = document.getElementById('ingredients-container');
                const div = document.createElement('div');
                div.innerHTML = createIngredientRow();
                container.appendChild(div.firstElementChild);
            }

            function addIngredientRowWithValues(name, quantity, unit) {
                const container = document.getElementById('ingredients-container');
                const div = document.createElement('div');
                div.innerHTML = createIngredientRow(name, quantity, unit);
                container.appendChild(div.firstElementChild);
            }

            function removeIngredientRow(button) {
                const container = document.getElementById('ingredients-container');
                if (container.children.length > 1) {
                    button.closest('.ingredient-row').remove();
                } else {
                    alert('Recipe must have at least one ingredient');
                }
            }

            // Preparation Steps Functions
            function handleSteps(recipe) {
                const container = document.getElementById('steps-container');
                container.innerHTML = '';

                if (recipe.preparation_step_tbl) {
                    const steps = recipe.preparation_step_tbl
                        .split('\n')
                        .map(step => step.trim().replace(/^\d+\.\s*/, ''))
                        .filter(step => step);

                    steps.forEach((step, index) => {
                        if (index === 0) {
                            container.innerHTML = createStepRow(step, 1);
                        } else {
                            addStepWithValue(step);
                        }
                    });
                } else {
                    container.innerHTML = createStepRow('', 1);
                }
            }

            function createStepRow(step = '', number = 1) {
                const sanitizedStep = sanitizeInput(step);

                return `
                    <div class="step-row flex items-center gap-2">
                        <span class="text-gray-600">${number}.</span>
                        <textarea 
                            name="preparation_steps[]" 
                            placeholder="Enter step description" 
                            required
                            maxlength="1000"
                            class="flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500 resize-y min-h-[60px]"
                            oninput="validateStep(this)"
                        >${sanitizedStep}</textarea>
                        <button type="button" 
                            onclick="removeStep(this)"
                            class="text-red-500 hover:text-red-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }

            function addStep() {
                const container = document.getElementById('steps-container');
                const stepCount = container.children.length + 1;
                const div = document.createElement('div');
                div.innerHTML = createStepRow('', stepCount);
                container.appendChild(div.firstElementChild);
                renumberSteps();
            }

            function addStepWithValue(step) {
                const container = document.getElementById('steps-container');
                const stepCount = container.children.length + 1;
                const div = document.createElement('div');
                div.innerHTML = createStepRow(step, stepCount);
                container.appendChild(div.firstElementChild);
                renumberSteps();
            }

            function removeStep(button) {
                const container = document.getElementById('steps-container');
                if (container.children.length > 1) {
                    button.closest('.step-row').remove();
                    renumberSteps();
                } else {
                    alert('Recipe must have at least one preparation step');
                }
            }

            function renumberSteps() {
                const steps = document.querySelectorAll('#steps-container .step-row span');
                steps.forEach((step, index) => {
                    step.textContent = `${index + 1}.`;
                });
            }

            // Recipe CRUD Functions
            function editRecipe(recipe) {
                try {
                    // Fetch recipe data including ingredients
                    fetch(`get_recipe.php?recipe_id=${recipe.recipe_id}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.recipe) {
                                const recipeData = data.recipe;
                                
                                // Show the recipe modal
                                const modal = document.getElementById('recipeModal');
                                const form = document.getElementById('recipeForm');

                                if (!modal || !form) return;

                                // Reset form and show modal
                                form.reset();
                                modal.classList.remove('hidden');

                                // Clear containers
                                document.getElementById('ingredients-container').innerHTML = '';
                                document.getElementById('steps-container').innerHTML = '';

                                // Populate fields with sanitized data
                                document.getElementById('recipe_id').value = recipeData.recipe_id || '';
                                document.getElementById('recipe_name').value = sanitizeInput(recipeData.recipe_name) || '';

                                // Handle equipment selection
                                const equipmentSelect = document.getElementById('equipment');
                                if (equipmentSelect && recipeData.equipment_name) {
                                    Array.from(equipmentSelect.options).forEach(option => {
                                        if (option.value === recipeData.equipment_name) {
                                            option.selected = true;
                                            option.disabled = false;
                                        }
                                    });

                                    // If equipment not in list, add it
                                    if (!Array.from(equipmentSelect.options).some(opt => opt.value === recipeData.equipment_name)) {
                                        const option = new Option(recipeData.equipment_name, recipeData.equipment_name, true, true);
                                        option.disabled = false;
                                        equipmentSelect.add(option);
                                    }
                                }

                                // Handle ingredients
                                if (recipeData.ingredients && Array.isArray(recipeData.ingredients)) {
                                    recipeData.ingredients.forEach(ingredient => {
                                        addIngredientRowWithValues(
                                            ingredient.ingredient_name,
                                            ingredient.quantity,
                                            ingredient.unit
                                        );
                                    });
                                } else {
                                    addIngredientRow(); // Add empty row if no ingredients
                                }

                                // Handle preparation steps
                                if (recipe.preparation_step_tbl) {
                                    const steps = recipe.preparation_step_tbl
                                        .split('\n')
                                        .map(step => step.trim())
                                        .map(step => step.replace(/^\d+\.\s*/, ''))
                                        .filter(step => step);

                                    steps.forEach((step, index) => {
                                        if (index === 0) {
                                            document.getElementById('steps-container').innerHTML = createStepRow(step, 1);
                                        } else {
                                            addStepWithValue(step);
                                        }
                                    });
                                } else {
                                    addStep();
                                }

                                // Validate all fields after population
                                const recipeNameInput = document.querySelector('[name="recipe_name"]');
                                if (recipeNameInput) {
                                    validateRecipeName(recipeNameInput);
                                }

                                document.querySelectorAll('[name="preparation_steps[]"]').forEach(step => {
                                    validateStep(step);
                                });

                            } else {
                                throw new Error(data.message || 'Failed to load recipe data');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching recipe data:', error);
                            alert('Error loading recipe: ' + error.message);
                        });
                } catch (error) {
                    console.error('Error editing recipe:', error);
                    alert('Error editing recipe: ' + error.message);
                }
            }

            function deleteRecipe(id) {
                if (confirm('Are you sure you want to delete this recipe?')) {
                    fetch(`delete_recipe.php?id=${id}`, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                alert(data.message);
                                // Remove the recipe element from the DOM
                                const recipeElement = document.querySelector(`[data-recipe-id="${id}"]`);
                                if (recipeElement) {
                                    recipeElement.remove();
                                } else {
                                    // If element not found, refresh the page
                                    window.location.reload();
                                }
                            } else {
                                throw new Error(data.error || 'Failed to delete recipe');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error deleting recipe: ' + error.message);
                        });
                }
            }

            // Modal Outside Click Handler
            window.onclick = function(event) {
                const modal = document.getElementById('recipeModal');
                if (event.target === modal) {
                    closeRecipeModal();
                }
            }

            function deleteSchedule(scheduleId) {
                if (confirm('Are you sure you want to delete this schedule?')) {
                    window.location.href = `delete_schedule.php?schedule_id=${scheduleId}`;
                }
            }

            // Add this function to handle recipe selection
            document.getElementById('product').addEventListener('change', async function() {
                const recipeId = this.value;
                const productionDate = document.getElementById('production_date').value;
                const scheduleId = document.getElementById('schedule_id').value;

                if (!recipeId || !productionDate) return;

                try {
                    // Fetch available equipment
                    const equipmentSelect = document.getElementById('equipment_id');
                    if (equipmentSelect) {
                        const currentEquipId = equipmentSelect.value;
                        const currentEquipText = equipmentSelect.options[equipmentSelect.selectedIndex]?.text;

                        const response = await fetch(`get_available_equipment.php`);
                        const data = await response.json();

                        // Add other available equipment
                        if (data.success && data.data) {
                            data.data.forEach(equip => {
                                if (equip.equipment_id !== currentEquipId) {
                                    equipmentSelect.add(new Option(equip.equipment_name, equip.equipment_id));
                                }
                            });
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error loading equipment: ' + error.message);
                }
            });

            // Enhanced ingredient name validation
            function validateIngredientName(input) {
                try {
                    const value = input.value.trim();

                    // Check for empty value
                    if (!value) {
                        input.setCustomValidity('Ingredient name cannot be empty');
                        input.reportValidity();
                        return;
                    }

                    // Check for XSS patterns first
                    const sanitizedValue = sanitizeInput(value);
                    if (sanitizedValue !== value) {
                        input.setCustomValidity('Invalid characters detected in ingredient name');
                        input.reportValidity();
                        input.value = sanitizedValue;
                        return;
                    }

                    // Check length
                    if (value.length > 50) {
                        input.setCustomValidity('Ingredient name is too long (maximum 50 characters)');
                        input.reportValidity();
                        return;
                    }

                    // Only allow letters, numbers, spaces, and basic punctuation
                    if (!/^[A-Za-z0-9\s\-.,()]{1,50}$/.test(value)) {
                        input.setCustomValidity('Ingredient name can only contain letters, numbers, spaces, and basic punctuation (.,-)()');
                        input.reportValidity();
                        return;
                    }

                    input.setCustomValidity('');
                } catch (error) {
                    input.setCustomValidity(error.message);
                    input.reportValidity();
                }
            }
        </script>
</body>

</html>