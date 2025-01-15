<?php
session_start();
require_once 'db_connect.php';
echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #f3f4f6; padding: 8px; border-radius: 4px; z-index: 50;">PHP Version: ' . phpversion() . '</div>';
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
                    <!-- Recipe Detail Modal -->
                    <div id="recipeDetailModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                            <div class="flex justify-between items-center mb-4">
                                <h3 id="modalTitle" class="text-2xl font-semibold text-gray-800"></h3>
                                <button onclick="closeRecipeDetail()" class="text-gray-600 hover:text-gray-800">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div id="modalContent" class="mt-4"></div>
                        </div>
                    </div>
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
                        $recipe_sql = "SELECT r.*, GROUP_CONCAT(i.ingredient_name SEPARATOR ', ') AS ingredients
                                       FROM recipe_db r
                                       LEFT JOIN recipe_ingredients i ON r.recipe_id = i.recipe_id
                                       GROUP BY r.recipe_id
                                       ORDER BY r.created_at DESC";
                        $stmt = $conn->prepare($recipe_sql);
                        $stmt->execute();
                        $recipe_result = $stmt->get_result();

                        if ($recipe_result->num_rows > 0) {
                            while ($row = $recipe_result->fetch_assoc()) {
                    ?>
                                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                    <!-- Card Header -->
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="text-xl font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($row['recipe_name']); ?>
                                            </h3>
                                            <span class="bg-pink-100 text-pink-800 text-xs px-2 py-1 rounded-full flex-shrink-0">
                                                Recipe #<?php echo $row['recipe_id']; ?>
                                            </span>
                                        </div>

                                        <!-- Card Content -->
                                        <div class="space-y-4">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Ingredients:</h4>
                                                <p class="text-gray-800 line-clamp-2"><?php echo htmlspecialchars($row['ingredients']); ?></p>
                                            </div>

                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Equipment Needed:</h4>
                                                <p class="text-gray-800 line-clamp-2"><?php echo nl2br(htmlspecialchars($row['equipment_tbl'])); ?></p>
                                            </div>

                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Preparation Preview:</h4>
                                                <p class="text-gray-800 line-clamp-3"><?php
                                                                                        $preview = substr($row['preparation_step_tbl'], 0, 100);
                                                                                        echo nl2br(htmlspecialchars($preview)) . (strlen($row['preparation_step_tbl']) > 100 ? '...' : '');
                                                                                        ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Card Footer - Always at bottom -->
                                    <div class="border-t border-gray-100">
                                        <button onclick='showRecipeDetail(<?php echo json_encode($row); ?>)'
                                            class="w-full bg-gray-50 px-6 py-3 text-pink-600 hover:text-pink-700 hover:bg-gray-100 font-medium text-sm transition-colors">
                                            View Details <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
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
                <h2 class="text-2xl font-semibold mb-6">Production Schedule</h2>
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
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='py-4 px-4 text-center text-gray-500'>No production schedules found</td></tr>";
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
                    <form action="process_batch.php" method="POST" class="space-y-4" id="addBatchForm">
                        <input type="hidden" name="production_id" id="production_id" value="">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Production Schedule</label>
                                <select name="production_id"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500"
                                    onchange="loadProductionDetails(this.value)">
                                    <option value="">Select Production Schedule</option>
                                    <?php
                                    $prod_sql = "SELECT p.production_id, r.recipe_name, p.production_date 
                                                FROM production_db p 
                                                JOIN recipe_db r ON p.recipe_id = r.recipe_id 
                                                WHERE p.production_id NOT IN (
                                                    SELECT production_id 
                                                    FROM batch_db 
                                                    WHERE production_id IS NOT NULL
                                                )
                                                ORDER BY p.production_date DESC";
                                    $prod_result = $conn->query($prod_sql);

                                    while ($row = $prod_result->fetch_assoc()) {
                                        echo "<option value='" . $row['production_id'] . "'>"
                                            . htmlspecialchars($row['recipe_name'])
                                            . " (" . date('Y-m-d', strtotime($row['production_date'])) . ")"
                                            . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Batch Number</label>
                                <input type="text"
                                    name="batch_no_tbl"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="datetime-local"
                                    name="startDate_tbl"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="datetime-local"
                                    name="endDate_tbl"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                            </div>
                        </div>



                        <!-- Update the Worker Section in Add Batch Form -->
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assigned Worker</label>
                                <textarea name="worker_names" id="worker_names" rows="1" required
                                    class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:ring-2 focus:ring-pink-500"
                                    placeholder="Worker will be loaded from production schedule"
                                    readonly></textarea>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Production Stage</label>
                                <select name="production_stage_tbl" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500">
                                    <option value="">Select Stage</option>
                                    <option value="Preparation">Preparation</option>
                                    <option value="Mixing">Mixing</option>
                                    <option value="Baking">Baking</option>
                                    <option value="Cooling">Cooling</option>
                                    <option value="Packaging">Packaging</option>
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

                        <!-- Quantity Check Section -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Target Quantity</label>
                                <input type="number" name="target_quantity" id="target_quantity" min="1" required
                                    class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:ring-2 focus:ring-pink-500"
                                    readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Actual Quantity</label>
                                <input type="number" name="actual_quantity" id="actual_quantity" min="0" required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-pink-500"
                                    onchange="calculateDefects()">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Defect Count</label>
                                <input type="number" name="defect_count" id="defect_count" min="0" required
                                    class="w-full px-4 py-2 border rounded-lg bg-gray-100 focus:ring-2 focus:ring-pink-500"
                                    readonly>
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
                                <th class="px-6 py-3 text-left">Production Schedule</th>
                                <th class="px-6 py-3 text-left">Start Date</th>
                                <th class="px-6 py-3 text-left">End Date</th>
                                <th class="px-6 py-3 text-left">Workers</th>
                                <th class="px-6 py-3 text-left">Production Stage</th>
                                <th class="px-6 py-3 text-left">Quality Check</th>
                                <th class="px-6 py-3 text-left">Quantities</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">

                            <?php
                            try {
                                $batch_sql = "SELECT b.*, 
                                        r.recipe_name,
                                        p.production_id,
                                        DATE_FORMAT(p.production_date, '%Y-%m-%d') as production_date,
                                        br.worker_count, 
                                        br.worker_names,
                                        br.temperature,
                                        br.moisture,
                                        br.weight,
                                        br.target_quantity,
                                        br.actual_quantity,
                                        br.defect_count
                                FROM batch_db b 
                                LEFT JOIN production_db p ON b.production_id = p.production_id
                                LEFT JOIN recipe_db r ON p.recipe_id = r.recipe_id
                                LEFT JOIN batch_reports br ON b.batch_no_tbl = br.batch_no
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

                                        echo "<tr data-batch-id='" . htmlspecialchars($row['batch_no_tbl']) . "' class='hover:bg-gray-50'>";
                                        echo "<td class='px-6 py-4 font-medium text-gray-900'>" . htmlspecialchars($row['batch_no_tbl']) . "</td>";

                                        // Updated Production Schedule column
                                        if ($row['production_id']) {
                                            echo "<td class='px-6 py-4'>" .
                                                htmlspecialchars($row['recipe_name']) .
                                                " (" . date('Y-m-d', strtotime($row['production_date'])) . ")" .
                                                "<br><span class='text-sm text-gray-500'>Schedule #" . htmlspecialchars($row['production_id']) . "</span></td>";
                                        } else {
                                            echo "<td class='px-6 py-4 text-gray-500'>No production schedule assigned</td>";
                                        }
                                        echo "<td class='px-6 py-4'>" . date('Y-m-d H:i', strtotime($row['startDate_tbl'])) . "</td>";
                                        echo "<td class='px-6 py-4'>" . ($row['endDate_tbl'] ? date('Y-m-d H:i', strtotime($row['endDate_tbl'])) : '-') . "</td>";

                                        // Worker details
                                        echo "<td class='px-6 py-4'>
                                                <div class='text-sm'>
                                                    <p>" . htmlspecialchars($row['worker_names']) . "</p>
                                                </div>
                                            </td>";

                                        echo "<td class='px-6 py-4'>" . ucwords(htmlspecialchars($row['production_stage_tbl'])) . "</td>";

                                        // Quality check details
                                        echo "<td class='px-6 py-4'>
                                                <div class='text-sm'>
                                                    <p><strong>Temperature:</strong> " . htmlspecialchars($row['temperature']) . "°C</p>
                                                    <p><strong>Moisture:</strong> " . htmlspecialchars($row['moisture']) . "%</p>
                                                    <p><strong>Weight:</strong> " . htmlspecialchars($row['weight']) . "g</p>
                                                    <p><strong>Notes:</strong> " . nl2br(htmlspecialchars($row['quality_check_tbl'])) . "</p>
                                                </div>
                                            </td>";

                                        // Quantity details
                                        echo "<td class='px-6 py-4'>
                                                <div class='text-sm'>
                                                    <p><strong>Target:</strong> " . htmlspecialchars($row['target_quantity']) . "</p>
                                                    <p><strong>Actual:</strong> " . htmlspecialchars($row['actual_quantity']) . "</p>
                                                    <p><strong>Defects:</strong> " . htmlspecialchars($row['defect_count']) . "</p>
                                                </div>
                                            </td>";

                                        echo "<td class='px-6 py-4'>
                                                <span class='px-2 py-1 rounded-full text-xs font-medium {$status_class}'>
                                                    {$row['status_tbl']}
                                                </span>
                                            </td>";

                                        echo "<td class='px-6 py-4'>
                                                <div class='flex items-center gap-2'>
                                                    <button onclick='editBatch(" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ")' 
                                                            class='bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition-colors'>
                                                        <i class='fas fa-edit'></i> Edit
                                                    </button>
                                                    <button onclick='deleteBatch(" . json_encode($row['batch_no_tbl']) . ")' 
                                                            class='bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition-colors'>
                                                        <i class='fas fa-trash'></i> Delete
                                                    </button>
                                                </div>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='10' class='px-6 py-4 text-center text-gray-500'>No batch records found</td></tr>";
                                }

                                $stmt->close();
                            } catch (Exception $e) {
                                echo "<tr><td colspan='10' class='px-6 py-4 text-center text-red-500'>Error loading batch records: " . $e->getMessage() . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>


                <!-- Add Batch Edit Modal -->
                <div id="batchEditModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 overflow-y-auto">
                    <div class="min-h-screen px-4 text-center">
                        <!-- Modal panel -->
                        <div class="inline-block w-full max-w-3xl my-8 text-left align-middle bg-white rounded-lg shadow-xl transform transition-all">
                            <!-- Header -->
                            <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 rounded-t-lg">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-xl font-semibold text-gray-900">Update Batch</h3>
                                    <button onclick="closeBatchModal()" class="text-gray-400 hover:text-gray-500">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Scrollable content -->
                            <div class="max-h-[calc(100vh-200px)] overflow-y-auto p-6">
                                <form id="editBatchForm" action="update_batch.php" method="POST" class="space-y-6">
                                    <input type="hidden" id="edit_batch_id" name="batch_no_tbl">

                                    <!-- Basic Information -->
                                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-medium text-gray-900">Basic Information</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Batch Number</label>
                                                <input type="text" id="edit_batch_no" name="batch_no_tbl" required readonly
                                                    class="w-full px-3 py-2 border rounded-lg bg-gray-100">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                                <select id="edit_status" name="status_tbl" required
                                                    class="w-full px-3 py-2 border rounded-lg">
                                                    <option value="In Progress">In Progress</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Scheduled">Scheduled</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dates -->
                                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-medium text-gray-900">Schedule</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                                <input type="datetime-local" id="edit_start_date" name="startDate_tbl" required
                                                    class="w-full px-3 py-2 border rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                                <input type="datetime-local" id="edit_end_date" name="endDate_tbl"
                                                    class="w-full px-3 py-2 border rounded-lg">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Update the Worker Section in Edit Batch Modal -->
                                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-medium text-gray-900">Worker</h4>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Worker</label>
                                            <textarea id="edit_worker_names" name="worker_names" rows="1" readonly
                                                class="w-full px-3 py-2 border rounded-lg bg-gray-100"></textarea>
                                        </div>
                                    </div>

                                    <!-- Production Details -->
                                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-medium text-gray-900">Production Details</h4>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Production Stage</label>
                                            <select id="edit_production_stage" name="production_stage_tbl" required
                                                class="w-full px-3 py-2 border rounded-lg">
                                                <option value="Preparation">Preparation</option>
                                                <option value="Mixing">Mixing</option>
                                                <option value="Baking">Baking</option>
                                                <option value="Cooling">Cooling</option>
                                                <option value="Packaging">Packaging</option>
                                            </select>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Quantity</label>
                                                <input type="number" id="edit_target_quantity" name="target_quantity" readonly
                                                    class="w-full px-3 py-2 border rounded-lg bg-gray-100">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Actual Quantity</label>
                                                <input type="number" id="edit_actual_quantity" name="actual_quantity" required
                                                    class="w-full px-3 py-2 border rounded-lg"
                                                    onchange="calculateEditDefects()">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Defect Count</label>
                                                <input type="number" id="edit_defect_count" name="defect_count" readonly
                                                    class="w-full px-3 py-2 border rounded-lg bg-gray-100">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Quality Check -->
                                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-medium text-gray-900">Quality Check</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                                                <input type="number" id="edit_temperature" name="temperature" step="0.1"
                                                    class="w-full px-3 py-2 border rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Moisture (%)</label>
                                                <input type="number" id="edit_moisture" name="moisture" step="0.1"
                                                    class="w-full px-3 py-2 border rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (g)</label>
                                                <input type="number" id="edit_weight" name="weight" step="0.1"
                                                    class="w-full px-3 py-2 border rounded-lg">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Quality Notes</label>
                                            <textarea id="edit_quality_check" name="quality_check_tbl" rows="3"
                                                class="w-full px-3 py-2 border rounded-lg"
                                                placeholder="Enter quality check notes..."></textarea>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Footer -->
                            <div class="sticky bottom-0 bg-white px-6 py-4 border-t border-gray-200 rounded-b-lg">
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="closeBatchModal()"
                                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                        Cancel
                                    </button>
                                    <button type="submit" form="editBatchForm"
                                        class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
                                        Update Batch
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Get DOM elements
                    const recipeSearch = document.getElementById('recipeSearch');
                    const recipeSort = document.getElementById('recipeSort');
                    const recipeGrid = document.querySelector('.grid');
                    const recipeCards = Array.from(recipeGrid.children);

                    // Store original order of cards
                    const originalCards = [...recipeCards];

                    // Add event listeners
                    recipeSearch.addEventListener('input', filterAndSortRecipes);
                    recipeSort.addEventListener('change', filterAndSortRecipes);

                    function filterAndSortRecipes() {
                        let filteredCards = filterRecipes();
                        sortRecipes(filteredCards);
                    }

                    function filterRecipes() {
                        const searchTerm = recipeSearch.value.toLowerCase();

                        const filteredCards = originalCards.filter(card => {
                            const recipeName = card.querySelector('h3').textContent.toLowerCase();
                            const ingredients = card.querySelector('p').textContent.toLowerCase();
                            return recipeName.includes(searchTerm) || ingredients.includes(searchTerm);
                        });

                        // Hide all cards first
                        originalCards.forEach(card => card.style.display = 'none');

                        // Show filtered cards
                        filteredCards.forEach(card => card.style.display = 'block');

                        return filteredCards;
                    }

                    function sortRecipes(cards) {
                        const sortValue = recipeSort.value;

                        const sortedCards = [...cards].sort((a, b) => {
                            const nameA = a.querySelector('h3').textContent.toLowerCase();
                            const nameB = b.querySelector('h3').textContent.toLowerCase();
                            const idA = parseInt(a.querySelector('.bg-pink-100').textContent.match(/\d+/)[0]);
                            const idB = parseInt(b.querySelector('.bg-pink-100').textContent.match(/\d+/)[0]);

                            switch (sortValue) {
                                case 'name':
                                    return nameA.localeCompare(nameB);
                                case 'newest':
                                    return idB - idA;
                                case 'oldest':
                                    return idA - idB;
                                default:
                                    return 0;
                            }
                        });

                        // Remove existing cards
                        recipeGrid.innerHTML = '';

                        // Append sorted cards
                        sortedCards.forEach(card => recipeGrid.appendChild(card));
                    }

                    // Show "No recipes found" message when search yields no results
                    function updateNoResultsMessage() {
                        const visibleCards = recipeGrid.querySelectorAll('div[style="display: block"]');
                        const noResultsMessage = document.querySelector('.no-results-message');

                        if (visibleCards.length === 0) {
                            if (!noResultsMessage) {
                                const message = document.createElement('div');
                                message.className = 'col-span-3 text-center text-gray-500 no-results-message';
                                message.textContent = 'No recipes found';
                                recipeGrid.appendChild(message);
                            }
                        } else if (noResultsMessage) {
                            noResultsMessage.remove();
                        }
                    }

                    // Initial sort
                    filterAndSortRecipes();

                    // Update the loadProductionDetails function
                    function loadProductionDetails(productionId) {
                        if (!productionId) return;

                        // Set the hidden production_id field
                        document.getElementById('production_id').value = productionId;

                        fetch(`get_production_details.php?id=${productionId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Auto-fill form fields
                                    document.getElementById('target_quantity').value = data.data.order_volume;
                                    document.getElementById('worker_names').value = data.data.staff_names;
                                    document.getElementById('startDate_tbl').value = data.data.production_date;

                                    // Reset actual quantity and calculate defects
                                    document.getElementById('actual_quantity').value = '';
                                    calculateDefects();
                                }
                            })
                            .catch(error => console.error('Error:', error));
                    }

                    function deleteBatch(batchId) {
                        if (confirm('Are you sure you want to delete this batch?')) {
                            // Create form data to send
                            const formData = new FormData();
                            formData.append('batch_id', batchId);

                            fetch('delete_batch.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Show success message 
                                        alert(data.message || 'Batch deleted successfully!');

                                        // Remove the batch element from the DOM
                                        const batchElement = document.querySelector(`[data-batch-id="${CSS.escape(batchId)}"]`);
                                        if (batchElement) {
                                            batchElement.remove();
                                        } else {
                                            // If DOM element not found, refresh the page
                                            window.location.reload();
                                        }
                                    } else {
                                        // Show error message
                                        throw new Error(data.error || 'Failed to delete batch');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error deleting batch: ' + error.message);
                                });
                        }
                    }

                    // Function to calculate defects in edit modal
                    function calculateDefects() {
                        const target = parseInt(document.getElementById('target_quantity').value) || 0;
                        const actual = parseInt(document.getElementById('actual_quantity').value) || 0;
                        const defects = Math.max(0, target - actual);
                        document.getElementById('defect_count').value = defects;
                    }

                    function calculateEditDefects() {
                        const target = parseInt(document.getElementById('edit_target_quantity').value) || 0;
                        const actual = parseInt(document.getElementById('edit_actual_quantity').value) || 0;
                        const defects = Math.max(0, target - actual);
                        document.getElementById('edit_defect_count').value = defects;
                    }
                    // Function to count workers in edit modal
                    function countEditWorkers() {
                        const workerNames = document.getElementById('edit_worker_names').value;
                        const workerCount = workerNames ? workerNames.split(',').filter(name => name.trim() !== '').length : 0;
                        document.getElementById('edit_worker_count').value = workerCount;
                    }

                    // Function to open edit modal with pre-filled batch data
                    // Update the editBatch function
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

                        document.getElementById('edit_worker_names').value = batch.worker_names || '';
                        document.getElementById('edit_target_quantity').value = batch.target_quantity || '';
                        document.getElementById('edit_actual_quantity').value = batch.actual_quantity || '';

                        // Calculate defects after setting values
                        calculateEditDefects();

                        document.getElementById('edit_temperature').value = batch.temperature || '';
                        document.getElementById('edit_moisture').value = batch.moisture || '';
                        document.getElementById('edit_weight').value = batch.weight || '';

                        document.getElementById('batchEditModal').classList.remove('hidden');
                    }

                    // Function to show specific content section

                    function showSection(sectionId) {
                        // Hide all content sections first
                        document.querySelectorAll('.content-section').forEach(section => {
                            section.classList.add('hidden');
                        });

                        // Show only the requested section
                        const targetSection = document.getElementById(`${sectionId}-section`);
                        if (targetSection) {
                            targetSection.classList.remove('hidden');
                        }

                        // Update active state of navigation items
                        document.querySelectorAll('.nav-item').forEach(item => {
                            if (item.getAttribute('href') === `#${sectionId}`) {
                                item.classList.add('bg-gray-700');
                            } else {
                                item.classList.remove('bg-gray-700');
                            }
                        });
                        // Update the showSection function
                        document.addEventListener('DOMContentLoaded', function() {
                            // Check for section parameter in URL
                            const urlParams = new URLSearchParams(window.location.search);
                            const sectionParam = urlParams.get('section');
                            // Get the hash from URL, or use section parameter, or default to 'recipe'
                            const currentSection = window.location.hash.slice(1) || sectionParam || 'recipe';
                            showSection(currentSection);
                        });
                    }



                    // Function to show recipe details in modal
                    function showRecipeDetail(recipe) {
                        const modal = document.getElementById('recipeDetailModal');
                        const modalContent = document.getElementById('modalContent');

                        document.getElementById('modalTitle').textContent = recipe.recipe_name;


                        modal.classList.remove('hidden');
                    }

                    // Function to close recipe modal
                    function closeRecipeModal() {
                        document.getElementById('recipeDetailModal').classList.add('hidden');
                    }

                    // Function to update batch status
                    function updateBatchStatus(batchId, status) {
                        if (confirm('Are you sure you want to update this batch status?')) {
                            fetch('update_batch.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
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

                    // Event listeners for modal and ESC key
                    window.onclick = function(event) {
                        const batchModal = document.getElementById('batchEditModal');
                        const recipeModal = document.getElementById('recipeDetailModal');
                        if (event.target === batchModal) closeBatchModal();
                        if (event.target === recipeModal) closeRecipeModal();
                    };

                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            if (!document.getElementById('recipeDetailModal').classList.contains('hidden')) closeRecipeModal();
                            if (!document.getElementById('batchEditModal').classList.contains('hidden')) closeBatchModal();
                        }
                    });

                    // Function to close batch modal
                    function closeBatchModal() {
                        document.getElementById('batchEditModal').classList.add('hidden');
                    }

                    // Add form submission handler for the add batch form
                    document.getElementById('addBatchForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        const form = e.target;
                        const formData = new FormData(form);

                        fetch(form.action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            if (data.success) {
                                window.location.href = 'baker_dashboard.php?section=batch#batch';
                                window.location.reload();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while adding the batch');
                        });
                    });
                </script>



                <script>
                    function showRecipeDetail(recipe) {
                        const modal = document.getElementById('recipeDetailModal');
                        const modalContent = document.getElementById('modalContent');

                        // Update modal title
                        document.getElementById('modalTitle').textContent = recipe.recipe_name;

                        // Update modal content
                        modalContent.innerHTML = `
        <div class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-lg mb-2 text-gray-800">Ingredients</h4>
                <p class="text-gray-700">${recipe.ingredients || 'No ingredients listed'}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-lg mb-2 text-gray-800">Equipment Needed</h4>
                <p class="text-gray-700">${(recipe.equipment_tbl || '').replace(/\n/g, '<br>')}</p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-lg mb-2 text-gray-800">Preparation Steps</h4>
                <div class="text-gray-700 space-y-2">
                    ${(recipe.preparation_step_tbl || '').split('\n').map((step, index) => 
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
                        <span class="text-gray-700 ml-2">#${recipe.recipe_id}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Created:</span>
                        <span class="text-gray-700 ml-2">${recipe.created_at || 'N/A'}</span>
                    </div>
                </div>
            </div>
        </div>
    `;

                        // Show the modal
                        modal.classList.remove('hidden');
                    }

                    function closeRecipeDetail() {
                        const modal = document.getElementById('recipeDetailModal');
                        modal.classList.add('hidden');
                    }
                </script>