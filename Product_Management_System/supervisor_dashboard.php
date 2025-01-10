<?php
session_start();
require_once 'db_connect.php';

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
                <button onclick="switchTab('recipe')" class="nav-item w-full text-left flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-book w-6"></i>
                    <span>Recipe Management</span>
                </button>
                <button onclick="switchTab('production')" class="nav-item w-full text-left flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-industry w-6"></i>
                    <span>Production Schedule</span>
                </button>
                <button onclick="switchTab('batch')" class="nav-item w-full text-left flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
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
        <div class="ml-64 flex-1 p-8">
            <!-- Recipe Section -->
            <div id="recipe-tab" class="content-section active">
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
                                            $ingredients_html .= "<div>{$name} - {$quantity} {$unit}</div>";
                                        }
                                    }

                                    echo "<tr class='hover:bg-gray-50'>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['recipe_name']) . "</td>";
                                    echo "<td class='px-6 py-4'>" . $ingredients_html . "</td>";
                                    echo "<td class='px-6 py-4'>" . nl2br(htmlspecialchars($row['preparation_step_tbl'])) . "</td>";
                                    echo "<td class='px-6 py-4'>" . htmlspecialchars($row['equipment_tbl']) . "</td>";
                                    echo "<td class='px-6 py-4'>
                                            <button onclick='editRecipe(" . json_encode($row) . ")' 
                                                    data-recipe='" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . "'
                                                    class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors mr-2'>
                                                <i class='fas fa-edit'></i> Edit
                                            </button>
                                            <button onclick='deleteRecipe(" . $row['recipe_id'] . ")' 
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
            </div>

            <!-- Production Section -->
            <div id="production-tab" class="content-section">
                <h2 class="text-2xl font-semibold mb-6">Production Schedule</h2>
                <button type="button" id="addScheduleBtn" onclick="showScheduleModal()"
                    class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2 mb-6">
                    <i class="fas fa-plus"></i> Add New Schedule
                </button>

                <!-- Production Schedule Table -->
                <table class="w-full bg-white rounded-lg shadow">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left">Product</th>
                            <th class="px-6 py-3 text-left">Date</th>
                            <th class="px-6 py-3 text-left">Order Volume</th>
                            <th class="px-6 py-3 text-left">Production Capacity</th>
                            <th class="px-6 py-3 text-left">Staff Assigned</th>
                            <th class="px-6 py-3 text-left">Equipment</th>
                            <th class="px-6 py-3 text-left">Equipment Status</th>
                            <th class="px-6 py-3 text-left">Total Ingredients</th>
                            <th class="px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.*, r.recipe_name, r.equipment_tbl,
                               (
                                   SELECT GROUP_CONCAT(a2.name_tbl)
                                   FROM admin_db a2
                                   WHERE FIND_IN_SET(a2.admin_id, p.staff_availability)
                               ) as staff_names,
                               CASE 
                                   WHEN p.production_date = CURDATE() THEN 'In Use'
                                   WHEN p.production_date > CURDATE() THEN 'Scheduled'
                                   WHEN p.production_date < CURDATE() THEN 'Available'
                                   ELSE es.status
                               END as equipment_status,
                               (
                                   SELECT GROUP_CONCAT(
                                       CONCAT(ri.ingredient_name, ': ', (ri.quantity * p.order_volume), ' ', ri.unit_tbl)
                                       SEPARATOR ', '
                                   )
                                   FROM recipe_ingredients ri
                                   WHERE ri.recipe_id = p.recipe_id
                               ) as total_ingredients
                               FROM production_db p 
                               LEFT JOIN recipe_db r ON p.recipe_id = r.recipe_id
                               LEFT JOIN equipment_status es ON es.equipment_id = p.equipment_id
                               GROUP BY p.production_id
                               ORDER BY p.production_date DESC";

                        $result = $conn->query($sql);

                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_class = match ($row['equipment_status']) {
                                    'Available' => 'text-green-600',
                                    'In Use' => 'text-yellow-600',
                                    'Scheduled' => 'text-blue-600',
                                    'Maintenance' => 'text-red-600',
                                    default => 'text-gray-600'
                                };

                                echo "<tr class='border-b hover:bg-gray-50'>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['recipe_name']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['production_date']) . "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['order_volume']) . " units</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['capacity']) . " units</td>";
                                echo "<td class='px-6 py-4'>" .
                                    (empty($row['staff_names']) ?
                                        '<span class="text-red-500">No staff assigned</span>' :
                                        htmlspecialchars($row['staff_names'])) .
                                    "</td>";
                                echo "<td class='px-6 py-4'>" . htmlspecialchars($row['equipment_tbl']) . "</td>";
                                echo "<td class='px-6 py-4 {$status_class} font-medium'>" .
                                    htmlspecialchars($row['equipment_status']) . "</td>";
                                echo "<td class='px-6 py-4 text-sm'>" . htmlspecialchars($row['total_ingredients']) . "</td>";
                                echo "<td class='px-6 py-4'>
                                        <button onclick='editSchedule(" . json_encode($row) . ")' 
                                            class='text-blue-600 hover:text-blue-800 mr-2'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <button onclick='deleteSchedule(" . $row['production_id'] . ")' 
                                            class='text-red-600 hover:text-red-800'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9' class='px-6 py-4 text-center'>No schedules found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Batch Section -->
            <div id="batch-tab" class="content-section">
                <h2 class="text-2xl font-semibold mb-6">Batch Reports</h2>
                <!-- Your existing batch content -->
            </div>

            <!-- Production Scheduling Section -->
            <div id="production-section" class="content-section hidden">
                <h2 class="text-2xl font-semibold mb-6">Production Scheduling</h2>
                <button onclick="showScheduleModal()"
                    class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors flex items-center gap-2 mb-6">
                    <i class="fas fa-plus"></i> Add New Schedule
                </button>

                <!-- Production Schedule Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Your existing table code -->
                </div>
            </div>
        </div>
    </div>

    <!-- Recipe Modal -->
    <div id="recipeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
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
                                COALESCE(
                                    CASE 
                                        WHEN EXISTS (
                                            SELECT 1 FROM recipe_db 
                                            WHERE recipe_db.equipment_tbl = es.equipment_name
                                        ) THEN 'In Use'
                                        ELSE es.status
                                    END, 'Available'
                                ) AS current_status
                            FROM equipment_status es
                            ORDER BY es.equipment_name";

                        $equipment_result = $conn->query($equipment_sql);
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
    <div id="scheduleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg max-w-2xl mx-auto mt-20 p-6">
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
                            echo "<option value='{$recipe['recipe_id']}'>{$recipe['recipe_name']}</option>";
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

                <!-- Equipment Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Equipment</label>
                    <select id="equipment_id" name="equipment_id" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="">Select Equipment</option>
                        <?php
                        $equipment_sql = "SELECT equipment_id, equipment_name FROM equipment_status WHERE status = 'Available'";
                        $equipment_result = $conn->query($equipment_sql);
                        while ($equipment = $equipment_result->fetch_assoc()) {
                            echo "<option value='{$equipment['equipment_id']}'>{$equipment['equipment_name']}</option>";
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
                    <button type="button" onclick="closeScheduleModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
                        Save Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.content-section').forEach(tab => {
                tab.style.display = 'none';
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';

            // Update active state of nav items
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('bg-gray-700');
            });

            // Add active state to clicked nav item
            event.currentTarget.classList.add('bg-gray-700');
        }

        // Show recipe tab by default
        document.addEventListener('DOMContentLoaded', function() {
            switchTab('recipe');
        });

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

        // Updated showRecipeModal function
        function showRecipeModal(recipe = null) {
            const modal = document.getElementById('recipeModal');
            const form = document.getElementById('recipeForm');

            if (!modal || !form) {
                console.error('Modal or form elements not found');
                return;
            }

            // Reset form and show modal
            form.reset();
            modal.classList.remove('hidden');

            // Initialize containers with default rows
            const ingredientsContainer = document.getElementById('ingredients-container');
            const stepsContainer = document.getElementById('steps-container');

            if (ingredientsContainer) {
                ingredientsContainer.innerHTML = createIngredientRow();
            }

            if (stepsContainer) {
                stepsContainer.innerHTML = createStepRow('', 1);
            }

            if (recipe) {
                // Editing existing recipe
                document.getElementById('recipe_id').value = recipe.recipe_id || '';
                document.getElementById('recipe_name').value = recipe.recipe_name || '';

                if (recipe.ingredient_data) {
                    handleIngredients(recipe);
                }

                if (recipe.preparation_step_tbl) {
                    handleSteps(recipe);
                }

                // Set equipment without async call
                const equipmentSelect = document.getElementById('equipment');
                if (equipmentSelect && recipe.equipment_tbl) {
                    equipmentSelect.value = recipe.equipment_tbl;
                }
            } else {
                // Adding new recipe
                document.getElementById('recipe_id').value = '';
            }
        }

        // Form submission handler
        document.getElementById('recipeForm').addEventListener('submit', function(e) {
            e.preventDefault();

            // Basic validation
            const recipeName = document.getElementById('recipe_name').value;
            const equipment = document.getElementById('equipment').value;

            if (!recipeName || !equipment) {
                alert('Please fill in all required fields');
                return;
            }

            // Submit the form
            this.submit();
        });

        // Close modal function
        function closeRecipeModal() {
            const modal = document.getElementById('recipeModal');
            if (modal) {
                modal.classList.add('hidden');
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
                        const response = await fetch('process_schedule.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();
                        if (result.success) {
                            alert('Schedule saved successfully!');
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

            select.innerHTML = '<option value="">Loading staff...</option>';

            // Fetch all bakers
            fetch('get_available_staff.php')
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Staff data received:', data);

                    select.innerHTML = '<option value="">Select Staff</option>';

                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(staff => {
                            const option = new Option(staff.name_tbl, staff.admin_id);
                            select.add(option);
                        });
                    } else {
                        select.innerHTML = '<option value="">No staff available</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading staff:', error);
                    select.innerHTML = '<option value="">Error loading staff</option>';
                });

            // Add remove button
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

            if (container.children.length >= 3) {
                alert('Maximum 3 staff members can be assigned');
                return;
            }

            container.appendChild(createStaffSelect());
        }

        // Ingredient Management Functions
        function handleIngredients(recipe) {
            const container = document.getElementById('ingredients-container');
            container.innerHTML = '';

            if (recipe.ingredient_data) {
                recipe.ingredient_data.split(';;').forEach((ingredient, index) => {
                    const [name, quantity, unit] = ingredient.split('|');
                    if (index === 0) {
                        container.innerHTML = createIngredientRow(name, quantity, unit);
                    } else {
                        addIngredientRowWithValues(name, quantity, unit);
                    }
                });
            } else {
                container.innerHTML = createIngredientRow();
            }
        }

        function createIngredientRow(name = '', quantity = '', unit = '') {
            return `
                <div class="ingredient-row flex gap-2 items-center">
                    <input type="text" name="ingredient_name[]" value="${name}" placeholder="Ingredient Name" required
                        class="flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    <input type="number" name="quantity[]" value="${quantity}" placeholder="Qty" required
                        class="w-24 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    <select name="unit[]" required
                        class="w-32 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                        <option value="">Unit</option>
                        <option value="g" ${unit === 'g' ? 'selected' : ''}>Grams (g)</option>
                        <option value="kg" ${unit === 'kg' ? 'selected' : ''}>Kilograms (kg)</option>
                        <option value="ml" ${unit === 'ml' ? 'selected' : ''}>Milliliters (ml)</option>
                        <option value="L" ${unit === 'L' ? 'selected' : ''}>Liters (L)</option>
                        <option value="pcs" ${unit === 'pcs' ? 'selected' : ''}>Pieces</option>
                        <option value="cups" ${unit === 'cups' ? 'selected' : ''}>Cups</option>
                        <option value="tbsp" ${unit === 'tbsp' ? 'selected' : ''}>Tablespoons</option>
                        <option value="tsp" ${unit === 'tsp' ? 'selected' : ''}>Teaspoons</option>
                    </select>
                    <button type="button" onclick="removeIngredientRow(this)"
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
            return `
                <div class="step-row flex items-center gap-2">
                    <span class="text-gray-600">${number}.</span>
                    <input type="text" name="preparation_steps[]" value="${step}"
                        placeholder="Enter step description" required
                        class="flex-grow px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    <button type="button" onclick="removeStep(this)"
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
            showRecipeModal(recipe);
        }

        function deleteRecipe(id) {
            if (confirm('Are you sure you want to delete this recipe?')) {
                window.location.href = `delete_recipe.php?id=${id}`;
            }
        }

        // Modal Outside Click Handler
        window.onclick = function(event) {
            const modal = document.getElementById('recipeModal');
            if (event.target === modal) {
                closeRecipeModal();
            }
        }

        function showScheduleModal(schedule = null) {
            const modal = document.getElementById('scheduleModal');
            const staffContainer = document.getElementById('staff-container');

            if (!modal || !staffContainer) {
                console.error("Modal or staff container not found");
                return;
            }

            modal.classList.remove('hidden');

            // Reset the form
            const form = document.getElementById('scheduleForm');
            if (form) form.reset();

            // Create initial staff dropdown
            const staffSelect = createStaffSelect();
            staffContainer.innerHTML = ''; // Clear existing content
            staffContainer.appendChild(staffSelect);

            // If editing an existing schedule
            if (schedule) {
                document.getElementById('schedule_id').value = schedule.schedule_id || '';
                document.getElementById('product').value = schedule.product_id || '';
                document.getElementById('production_date').value = schedule.production_date || '';
                document.getElementById('order_volume').value = schedule.order_volume || '';

                if (schedule.staff_names) {
                    const staffMembers = schedule.staff_names.split(',').map(s => s.trim());
                    staffMembers.forEach((staff, index) => {
                        if (index > 0) addStaff();
                    });
                }
            }
        }

        function addStaff() {
            const container = document.getElementById('staff-container');
            if (!container) {
                return;
            }

            // Clone the first select element
            const firstSelect = container.querySelector('select');
            if (firstSelect) {
                const newSelect = firstSelect.cloneNode(true);
                newSelect.value = ''; // Reset selection
                container.appendChild(newSelect);
            }
        }

        function deleteSchedule(scheduleId) {
            if (confirm('Are you sure you want to delete this schedule?')) {
                window.location.href = `delete_schedule.php?schedule_id=${scheduleId}`;
            }
        }

        // Add this function to handle recipe selection
        document.getElementById('product').addEventListener('change', function() {
            const recipeId = this.value;
            const orderVolume = document.getElementById('order_volume').value || 1;
            const productionDate = document.getElementById('production_date').value || new Date().toISOString().split('T')[0];

            if (recipeId) {
                fetch(`get_recipe.php?recipe_id=${recipeId}&volume=${orderVolume}&production_date=${productionDate}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update equipment
                            const equipmentSelect = document.getElementById('equipment_id');
                            if (equipmentSelect) {
                                equipmentSelect.value = data.recipe.equipment;
                            }

                            // Update staff options
                            const staffContainer = document.getElementById('staff-container');
                            if (staffContainer) {
                                staffContainer.innerHTML = ''; // Clear existing staff
                                const staffSelect = createStaffSelectWithOptions(data.recipe.available_staff);
                                staffContainer.appendChild(staffSelect);
                            }

                            // Show ingredients calculation
                            const ingredientsInfo = document.createElement('div');
                            ingredientsInfo.className = 'mt-2 text-sm text-gray-600';
                            ingredientsInfo.innerHTML = '<strong>Required Ingredients:</strong><br>' +
                                data.recipe.ingredients.map(ing =>
                                    `${ing.ingredient_name}: ${ing.quantity} ${ing.unit}`
                                ).join('<br>');

                            const orderVolumeInput = document.getElementById('order_volume');
                            orderVolumeInput.parentNode.appendChild(ingredientsInfo);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        });
    </script>
</body>

</html>