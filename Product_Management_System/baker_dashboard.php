<?php
session_start();
require_once 'db_connect.php';

// Check if user is logged in and is a baker
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'baker') {
    header("Location: login.html");
    exit();
}

try {
    // Get user details
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baker Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <div class="text-sm text-gray-400 text-center">Baker</div>
            </div>

            <nav class="mt-6">
                <a href="#recipe" onclick="showSection('recipe')"
                    class="nav-item flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-book w-6"></i>
                    <span>View Recipes</span>
                </a>
                <a href="#production" onclick="showSection('production')"
                    class="nav-item flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-industry w-6"></i>
                    <span>View Schedule</span>
                </a>
                <a href="#batch" onclick="showSection('batch')"
                    class="nav-item flex items-center px-6 py-3 hover:bg-gray-700 transition-colors">
                    <i class="fas fa-tasks w-6"></i>
                    <span>Batch Tracking</span>
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
            <!-- Recipe View Section -->
            <div id="recipe-section" class="content-section">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Recipe List</h2>
                    <p class="text-gray-600">View standardized recipes and preparation instructions</p>
                </div>

                <!-- Search and Filter -->
                <div class="mb-6 bg-white p-4 rounded-lg shadow">
                    <div class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" id="recipeSearch"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                                placeholder="Search recipes...">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <select id="recipeSort"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                <option value="">Sort by...</option>
                                <option value="name">Name (A-Z)</option>
                                <option value="newest">Newest First</option>
                                <option value="oldest">Oldest First</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Recipe Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php
                    try {
                        $recipe_sql = "SELECT * FROM recipe_db ORDER BY created_at DESC";
                        $stmt = $conn->prepare($recipe_sql);
                        $stmt->execute();
                        $recipe_result = $stmt->get_result();

                        if ($recipe_result->num_rows > 0) {
                            while ($row = $recipe_result->fetch_assoc()) {
                    ?>
                                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="text-xl font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($row['ingredient_name_tbl']); ?>
                                            </h3>
                                            <span class="bg-pink-100 text-pink-800 text-xs px-2 py-1 rounded-full">
                                                Recipe #<?php echo $row['ingredient_id']; ?>
                                            </span>
                                        </div>

                                        <div class="space-y-4">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Quantity Required:</h4>
                                                <p class="text-gray-800"><?php echo htmlspecialchars($row['quantity_tbl']); ?></p>
                                            </div>

                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Equipment Needed:</h4>
                                                <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($row['equipment_tbl'])); ?></p>
                                            </div>

                                            <div class="truncate">
                                                <h4 class="text-sm font-medium text-gray-500">Preparation Preview:</h4>
                                                <p class="text-gray-800"><?php
                                                                            $preview = substr($row['preparation_step_tbl'], 0, 100);
                                                                            echo nl2br(htmlspecialchars($preview)) . (strlen($row['preparation_step_tbl']) > 100 ? '...' : '');
                                                                            ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 px-6 py-3">
                                        <div class="flex justify-between items-center">

                                            <button onclick='showRecipeDetail(<?php echo json_encode($row); ?>)'
                                                class="text-pink-600 hover:text-pink-700 font-medium text-sm">
                                                View Details <i class="fas fa-chevron-right ml-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                    <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='px-6 py-4 text-center text-gray-500'>No recipes found</td></tr>";
                        }

                        $stmt->close();
                    } catch (Exception $e) {
                        echo "<tr><td colspan='4' class='px-6 py-4 text-center text-red-500'>Error loading recipes: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </div>
            </div>

            <!-- Production Section -->
            <div id="production-section" class="content-section hidden">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Production Schedule</h2>
                    <p class="text-gray-600">View current production schedules</p>
                </div>

                <!-- Production Schedule Content -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-800 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left">Order Volume</th>
                                <th class="px-6 py-3 text-left">Production Capacity</th>
                                <th class="px-6 py-3 text-left">Staff Availability</th>
                                <th class="px-6 py-3 text-left">Equipment Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            try {
                                $schedule_sql = "SELECT * FROM production_db ORDER BY created_at DESC";
                                $stmt = $conn->prepare($schedule_sql);
                                $stmt->execute();
                                $schedule_result = $stmt->get_result();

                                if ($schedule_result->num_rows > 0) {
                                    while ($row = $schedule_result->fetch_assoc()) {
                                        echo "<tr class='hover:bg-gray-50'>";
                                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['order_volumn_tbl']) . "</td>";
                                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['capacity_tbl']) . "</td>";
                                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['staff_availability_tbl']) . "</td>";
                                        echo "<td class='px-6 py-4'>" . htmlspecialchars($row['equipment_status_tbl']) . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500'>No schedules found</td></tr>";
                                }

                                $stmt->close();
                            } catch (Exception $e) {
                                echo "<tr><td colspan='5' class='px-6 py-4 text-center text-red-500'>Error loading schedules: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Batch Section -->
            <div id="batch-section" class="content-section hidden">
                <div class="mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800">Batch Tracking</h2>
                    <p class="text-gray-600">Track and manage production batches</p>
                </div>

                <!-- Add Batch Form -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4">Add New Batch</h3>
                    <form action="process_batch.php" method="POST" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                                <input type="text" name="batch_no_tbl" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="datetime-local" name="startDate_tbl" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="datetime-local" name="endDate_tbl"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Production Stage</label>
                                <select name="production_stage_tbl" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                    <option value="">Select Stage</option>
                                    <option value="preparation">Preparation</option>
                                    <option value="mixing">Mixing</option>
                                    <option value="baking">Baking</option>
                                    <option value="cooling">Cooling</option>
                                    <option value="packaging">Packaging</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status_tbl" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                    <option value="In Progress">In Progress</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Scheduled">Scheduled</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quality Check</label>
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Temperature (°C)</label>
                                        <input type="number" name="temperature" step="0.1"
                                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Moisture Content (%)</label>
                                        <input type="number" name="moisture" step="0.1"
                                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">Weight (g)</label>
                                        <input type="number" name="weight" step="0.1"
                                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Visual Inspection</label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <label class="flex items-center">
                                            <input type="checkbox" name="visual_checks[]" value="color" class="mr-2">
                                            Color
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="visual_checks[]" value="texture" class="mr-2">
                                            Texture
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="visual_checks[]" value="shape" class="mr-2">
                                            Shape
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="visual_checks[]" value="size" class="mr-2">
                                            Size
                                        </label>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Additional Notes</label>
                                    <textarea name="quality_check_tbl" rows="3"
                                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500"
                                        placeholder="Enter any additional quality check notes..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors">
                                Add Batch
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Batch List -->
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
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            try {
                                $batch_sql = "SELECT * FROM batch_db ORDER BY startDate_tbl DESC";
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
                                        echo "<td class='px-6 py-4'>
                                                <div class='flex items-center gap-2'>
                                                    <button onclick='editBatch(" . json_encode($row) . ")' 
                                                            class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors'>
                                                        <i class='fas fa-edit'></i> Edit
                                                    </button>
                                                    <button onclick='deleteBatch(\"" . $row['batch_no_tbl'] . "\")' 
                                                            class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors'>
                                                        <i class='fas fa-trash'></i> Delete
                                                    </button>
                                                </div>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='px-6 py-4 text-center text-gray-500'>No batch records found</td></tr>";
                                }

                                $stmt->close();
                            } catch (Exception $e) {
                                echo "<tr><td colspan='7' class='px-6 py-4 text-center text-red-500'>Error loading batch records: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update the Recipe Detail Modal -->
    <div id="recipeDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
        <div class="bg-white rounded-lg max-w-3xl mx-auto my-8 p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-semibold text-gray-800" id="modalTitle"></h3>
                <button onclick="closeRecipeDetail()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-6" id="modalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Add Batch Edit Modal -->
    <div id="batchEditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="bg-white rounded-lg max-w-3xl mx-auto mt-20 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Update Batch Status</h3>
                <button onclick="closeBatchModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editBatchForm" action="update_batch.php" method="POST" class="space-y-4">
                <input type="hidden" id="edit_batch_id" name="batch_no_tbl">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                        <input type="text" id="edit_batch_no" name="batch_no_tbl" required readonly
                            class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:ring-2 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="datetime-local" id="edit_start_date" name="startDate_tbl" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="datetime-local" id="edit_end_date" name="endDate_tbl"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Production Stage</label>
                        <select id="edit_production_stage" name="production_stage_tbl" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="preparation">Preparation</option>
                            <option value="mixing">Mixing</option>
                            <option value="baking">Baking</option>
                            <option value="cooling">Cooling</option>
                            <option value="packaging">Packaging</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quality Check</label>
                        <textarea id="edit_quality_check" name="quality_check_tbl" rows="3" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500"
                            placeholder="Enter quality check notes..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="edit_status" name="status_tbl" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Scheduled">Scheduled</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeBatchModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors">
                        Update Batch
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showSection(sectionName) {
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.add('hidden');
            });
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('bg-pink-600');
            });
            document.getElementById(sectionName + '-section').classList.remove('hidden');
            document.querySelector(`[href="#${sectionName}"]`).classList.add('bg-pink-600');
        }

        showSection('recipe');

        function closeRecipeModal() {
            const modal = document.getElementById('recipeModal');
            modal.classList.add('hidden');
        }

        // Add an event listener to close modals when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('recipeModal');
            if (event.target === modal) {
                closeRecipeModal();
            }
        };


        function showRecipeDetail(recipe) {
            const modal = document.getElementById('recipeDetailModal');
            const title = document.getElementById('modalTitle');
            const content = document.getElementById('modalContent');

            title.textContent = recipe.ingredient_name_tbl;
            content.innerHTML = `
            <div class="space-y-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2 text-gray-800">Quantity Required</h4>
                    <p class="text-gray-700">${recipe.quantity_tbl}</p>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2 text-gray-800">Equipment Needed</h4>
                    <p class="text-gray-700">${recipe.equipment_tbl.replace(/\n/g, '<br>')}</p>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2 text-gray-800">Preparation Steps</h4>
                    <div class="text-gray-700 space-y-2">
                        ${recipe.preparation_step_tbl.split('\n').map((step, index) => 
                            `<p class="flex gap-2">
                                <span class="font-medium text-pink-600">${index + 1}.</span>
                                <span>${step}</span>
                            </p>`
                        ).join('')}
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2 text-gray-800">Recipe Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Recipe ID:</span>
                            <span class="text-gray-700 ml-2">#${recipe.ingredient_id}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

            modal.classList.remove('hidden');
        }

        // Add search functionality
        document.getElementById('recipeSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const recipeCards = document.querySelectorAll('.grid > div');

            recipeCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Add sort functionality
        document.getElementById('recipeSort').addEventListener('change', function(e) {
            const sortBy = e.target.value;
            const recipeCards = Array.from(document.querySelectorAll('.grid > div'));
            const container = document.querySelector('.grid');

            recipeCards.sort((a, b) => {
                const nameA = a.querySelector('h3').textContent.toLowerCase();
                const nameB = b.querySelector('h3').textContent.toLowerCase();

                switch (sortBy) {
                    case 'name':
                        return nameA.localeCompare(nameB);
                    case 'newest':
                        return -1; // Assuming they're already sorted by newest
                    case 'oldest':
                        return 1; // Reverse the current order
                    default:
                        return 0;
                }
            });

            container.innerHTML = '';
            recipeCards.forEach(card => container.appendChild(card));
        });

        // Add this JavaScript function after your existing scripts
        function updateBatchStatus(batchId, status) {
            if (confirm('Are you sure you want to update this batch status?')) {
                fetch('update_batch.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `batch_id=${batchId}&status=${status}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Batch status updated successfully!');
                            location.reload();
                        } else {
                            alert('Error updating batch status');
                        }
                    });
            }
        }

        function editBatch(batch) {
            document.getElementById('edit_batch_id').value = batch.batch_no_tbl;
            document.getElementById('edit_batch_no').value = batch.batch_no_tbl;
            document.getElementById('edit_start_date').value = batch.startDate_tbl.slice(0, 16);
            if (batch.endDate_tbl) {
                document.getElementById('edit_end_date').value = batch.endDate_tbl.slice(0, 16);
            }
            document.getElementById('edit_production_stage').value = batch.production_stage_tbl;
            document.getElementById('edit_quality_check').value = batch.quality_check_tbl;
            document.getElementById('edit_status').value = batch.status_tbl;

            document.getElementById('batchEditModal').classList.remove('hidden');
        }

        function closeBatchModal() {
            document.getElementById('batchEditModal').classList.add('hidden');
        }

        function deleteBatch(batchId) {
            if (confirm('Are you sure you want to delete this batch?')) {
                fetch('delete_batch.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `batch_id=${batchId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Batch deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error deleting batch');
                        }
                    });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const batchModal = document.getElementById('batchEditModal');
            const recipeModal = document.getElementById('recipeDetailModal');

            if (event.target === batchModal) {
                closeBatchModal();
            }
            if (event.target === recipeModal) {
                closeRecipeDetail();
            }
        }

        // Add this after your existing JavaScript
        // Close modal when clicking outside
        document.getElementById('recipeDetailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRecipeDetail();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !document.getElementById('recipeDetailModal').classList.contains('hidden')) {
                closeRecipeDetail();
            }
        });
    </script>
</body>

</html>