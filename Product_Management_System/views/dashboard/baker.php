<?php
session_start();
require_once '../../includes/db_connect.php';
require_once '../../includes/security.php';

// Initialize Security class
$security = new Security();

// Check if user is logged in and is a baker
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'baker') {
    header("Location: ../../login.html");
    exit();
}

// Prevent session fixation
session_regenerate_id(true);

// Get user details with prepared statement
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM admin_db WHERE admin_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: ../../login.html");
    exit();
}

// Set security headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
header("Content-Security-Policy: default-src 'self' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com;");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baker Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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

            <a href="../../logout.php" onclick="return confirm('Are you sure you want to logout?');" 
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
                    <h2 class="text-2xl font-semibold">Recipe List</h2>
                    <p class="text-gray-600">View standardized recipes and preparation instructions</p>
                </div>

                <!-- Recipe Cards -->
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
                            while($row = $recipe_result->fetch_assoc()) {
                                // Sanitize all data before output
                                $recipe_name = $security->sanitizeInput($row['recipe_name']);
                                $recipe_id = (int)$row['recipe_id'];
                                $equipment = $security->sanitizeInput($row['equipment_tbl']);
                                $ingredients = $security->sanitizeInput($row['ingredients']);
                                $preparation_steps = $security->sanitizeInput($row['preparation_step_tbl']);
                                
                                // Encode data for JavaScript
                                $recipe_json = $security->jsonEncode([
                                    'recipe_id' => $recipe_id,
                                    'recipe_name' => $recipe_name,
                                    'equipment_tbl' => $equipment,
                                    'ingredients' => $ingredients,
                                    'preparation_step_tbl' => $preparation_steps,
                                    'created_at' => $row['created_at']
                                ]);
                                ?>
                                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                    <div class="p-6">
                                        <div class="flex justify-between items-start mb-4">
                                            <h3 class="text-xl font-semibold text-gray-800">
                                                <?php echo $recipe_name; ?>
                                            </h3>
                                            <span class="bg-pink-100 text-pink-800 text-xs px-2 py-1 rounded-full flex-shrink-0">
                                                Recipe #<?php echo $recipe_id; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="space-y-4">
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Ingredients:</h4>
                                                <p class="text-gray-800 line-clamp-2"><?php echo $ingredients; ?></p>
                                            </div>
                                            
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-500">Equipment Needed:</h4>
                                                <p class="text-gray-800 line-clamp-2"><?php echo nl2br($equipment); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-6 py-3">
                                        <button class="text-pink-600 hover:text-pink-700" 
                                                onclick="showRecipeDetail(<?php echo $recipe_json; ?>)">
                                            View Details <i class="fas fa-chevron-right ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<div class='col-span-full text-center text-gray-500'>No recipes available</div>";
                        }
                        $stmt->close();
                    } catch (Exception $e) {
                        error_log("Error in baker dashboard: " . $e->getMessage());
                        echo "<div class='col-span-full text-center text-red-500'>Error loading recipes. Please try again later.</div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Other sections will be added here -->
        </div>
    </div>

    <!-- Recipe Detail Modal -->
    <div id="recipeDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg max-w-3xl mx-auto mt-20 p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold" id="modalTitle"></h3>
                <button onclick="closeRecipeDetail()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="space-y-4" id="modalContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function showSection(sectionName) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.add('hidden');
        });
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('bg-pink-600');
            item.classList.remove('hover:bg-gray-700');
        });
        document.getElementById(sectionName + '-section').classList.remove('hidden');
        const activeNav = document.querySelector(`[href="#${sectionName}"]`);
        activeNav.classList.add('bg-pink-600');
        activeNav.classList.remove('hover:bg-gray-700');
    }

    function showRecipeDetail(recipeJson) {
        const recipe = JSON.parse(recipeJson);
        const modal = document.getElementById('recipeDetailModal');
        const title = document.getElementById('modalTitle');
        const content = document.getElementById('modalContent');

        title.textContent = recipe.recipe_name;
        content.innerHTML = `
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-lg mb-2">Ingredients</h4>
                <p>${recipe.ingredients}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-lg mb-2">Equipment Needed</h4>
                <p>${recipe.equipment_tbl.replace(/\n/g, '<br>')}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-lg mb-2">Preparation Steps</h4>
                <p>${recipe.preparation_step_tbl.replace(/\n/g, '<br>')}</p>
            </div>
        `;

        modal.classList.remove('hidden');
    }

    function closeRecipeDetail() {
        document.getElementById('recipeDetailModal').classList.add('hidden');
    }

    // Show recipe section by default
    document.addEventListener('DOMContentLoaded', function() {
        showSection('recipe');
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('recipeDetailModal');
        if (event.target === modal) {
            closeRecipeDetail();
        }
    }
    </script>
</body>
</html> 