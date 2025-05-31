<?php
// Check if user is already logged in BEFORE any output
if(app()->getAuthSession()->isLoggedIn()) {
    header("Location: " . url('account'));
    exit;
}

// Get mode (login or register)
$mode = isset($_GET['mode']) && $_GET['mode'] === 'register' ? 'register' : 'login';
$isAdminLogin = isset($_GET['admin']) && $_GET['admin'] === 'true';

// Initialize variables
$error = null;
$success = null;

// Handle form submission BEFORE any output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            // Handle login
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $loginType = $_POST['login_type'] ?? 'customer';
            
            $result = app()->getAuthSession()->authenticate($email, $password, $loginType);
            
            if ($result) {
                $_SESSION['flash_message'] = 'Welcome back, ' . $result['name'] . '!';
                $_SESSION['flash_type'] = 'success';
                
                // Redirect based on user type
                if ($loginType === 'admin') {
                    header("Location: " . url('admin/dashboard'));
                } else {
                    // Redirect to intended page or account
                    $redirect = $_SESSION['redirect_after_login'] ?? url('account');
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirect);
                }
                exit;
            } else {
                $error = $loginType === 'admin' ? 'Invalid username or password.' : 'Invalid email or password.';
            }
        } elseif ($_POST['action'] === 'register') {
            // Handle registration
            $userData = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'password' => $_POST['password'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? ''
            ];
            
            // Validate
            if (strlen($userData['password']) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } elseif ($_POST['password'] !== $_POST['confirm_password']) {
                $error = 'Passwords do not match.';
            } else {
                $result = app()->getAuthSession()->registerUser($userData);
                
                if ($result) {
                    $_SESSION['flash_message'] = 'Welcome to AWE Electronics, ' . $result['name'] . '!';
                    $_SESSION['flash_type'] = 'success';
                    header("Location: " . url('account'));
                    exit;
                } else {
                    $error = 'Email already exists. Please use a different email.';
                }
            }
        }
    }
}

// Set page title
$pageTitle = $isAdminLogin ? 'Admin Login' : 'Login / Register';

// NOW include header after all potential redirects
include 'includes/header.php';
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="container mx-auto px-4">
        <div class="max-w-md mx-auto">
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="<?php echo url(); ?>" class="inline-flex items-center space-x-2">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bolt text-white text-2xl"></i>
                    </div>
                    <span class="text-2xl font-bold gradient-text">AWE Electronics</span>
                </a>
            </div>
            
            <!-- Auth Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden" x-data="{ mode: '<?php echo $mode; ?>', isAdmin: <?php echo $isAdminLogin ? 'true' : 'false'; ?> }">
                <!-- Tab Switcher (only for customer login) -->
                <div class="flex border-b" x-show="!isAdmin">
                    <button @click="mode = 'login'" 
                            :class="mode === 'login' ? 'bg-primary-50 text-primary-600 border-b-2 border-primary-600' : 'text-gray-600 hover:bg-gray-50'"
                            class="flex-1 py-4 font-semibold transition">
                        Login
                    </button>
                    <button @click="mode = 'register'" 
                            :class="mode === 'register' ? 'bg-primary-50 text-primary-600 border-b-2 border-primary-600' : 'text-gray-600 hover:bg-gray-50'"
                            class="flex-1 py-4 font-semibold transition">
                        Register
                    </button>
                </div>
                
                <!-- Admin Login Header -->
                <div x-show="isAdmin" class="bg-gray-900 text-white p-6 text-center">
                    <i class="fas fa-user-shield text-4xl mb-2"></i>
                    <h2 class="text-2xl font-bold">Admin Login</h2>
                    <p class="text-gray-300 mt-2">Authorized personnel only</p>
                </div>
                
                <div class="p-8">
                    <?php if($error): ?>
                    <div class="bg-red-50 text-red-800 p-4 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                    <div class="bg-green-50 text-green-800 p-4 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" x-show="mode === 'login' || isAdmin" x-transition:enter="transition ease-out duration-300"
                          x-transition:enter-start="opacity-0 transform translate-y-4"
                          x-transition:enter-end="opacity-100 transform translate-y-0">
                        <input type="hidden" name="action" value="login">
                        <input type="hidden" name="login_type" :value="isAdmin ? 'admin' : 'customer'">
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">
                                <span x-text="isAdmin ? 'Username' : 'Email Address'"></span>
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="email" 
                                       required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       :placeholder="isAdmin ? 'admin' : 'john@example.com'"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" x-show="!isAdmin"></i>
                                <i class="fas fa-user-shield absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400" x-show="isAdmin"></i>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Password</label>
                            <div class="relative">
                                <input type="password" 
                                       name="password" 
                                       required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="••••••••">
                                <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between mb-6" x-show="!isAdmin">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember" class="mr-2 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-gray-600">Remember me</span>
                            </label>
                            <a href="<?php echo url('forgot-password'); ?>" class="text-primary-600 hover:text-primary-700 text-sm">
                                Forgot password?
                            </a>
                        </div>
                        
                        <button type="submit" class="w-full py-3 rounded-lg transition duration-300 font-semibold"
                                :class="isAdmin ? 'bg-gray-900 hover:bg-gray-800 text-white' : 'bg-primary-600 hover:bg-primary-700 text-white'">
                            <span x-text="isAdmin ? 'Admin Login' : 'Login'"></span>
                        </button>
                        
                        <div class="mt-6 text-center" x-show="!isAdmin">
                            <p class="text-gray-600">
                                Don't have an account? 
                                <button type="button" @click="mode = 'register'" class="text-primary-600 hover:text-primary-700 font-medium">
                                    Register here
                                </button>
                            </p>
                        </div>
                        
                        <div class="mt-6 text-center" x-show="isAdmin">
                            <a href="<?php echo url('login'); ?>" class="text-gray-600 hover:text-gray-800 text-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Customer Login
                            </a>
                        </div>
                    </form>
                    
                    <!-- Register Form -->
                    <form method="POST" x-show="mode === 'register' && !isAdmin" x-transition:enter="transition ease-out duration-300"
                          x-transition:enter-start="opacity-0 transform translate-y-4"
                          x-transition:enter-end="opacity-100 transform translate-y-0"
                          x-cloak>
                        <input type="hidden" name="action" value="register">
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Full Name</label>
                            <div class="relative">
                                <input type="text" 
                                       name="name" 
                                       required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="John Doe"
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Email Address</label>
                            <div class="relative">
                                <input type="email" 
                                       name="email" 
                                       required
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="john@example.com"
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Phone Number (Optional)</label>
                            <div class="relative">
                                <input type="tel" 
                                       name="phone"
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="+1 (555) 123-4567"
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                <i class="fas fa-phone absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Password</label>
                            <div class="relative">
                                <input type="password" 
                                       name="password" 
                                       required
                                       minlength="6"
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="••••••••">
                                <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Minimum 6 characters</p>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-medium mb-2">Confirm Password</label>
                            <div class="relative">
                                <input type="password" 
                                       name="confirm_password" 
                                       required
                                       minlength="6"
                                       class="w-full px-4 py-3 pl-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="••••••••">
                                <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" name="terms" required class="mr-2 mt-1 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-gray-600">
                                    I agree to the <a href="<?php echo url('terms'); ?>" class="text-primary-600 hover:underline">Terms of Service</a> 
                                    and <a href="<?php echo url('privacy'); ?>" class="text-primary-600 hover:underline">Privacy Policy</a>
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition duration-300 font-semibold">
                            Create Account
                        </button>
                        
                        <div class="mt-6 text-center">
                            <p class="text-gray-600">
                                Already have an account? 
                                <button type="button" @click="mode = 'login'" class="text-primary-600 hover:text-primary-700 font-medium">
                                    Login here
                                </button>
                            </p>
                        </div>
                    </form>
                    
                    <!-- Social Login Options -->
                    <div class="mt-8" x-show="!isAdmin">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">Or continue with</span>
                            </div>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-2 gap-4">
                            <button class="bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center justify-center hover:bg-gray-50 transition">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5 mr-2">
                                Google
                            </button>
                            <button class="bg-white border border-gray-300 rounded-lg px-4 py-2 flex items-center justify-center hover:bg-gray-50 transition">
                                <i class="fab fa-facebook text-blue-600 text-xl mr-2"></i>
                                Facebook
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Test Credentials & Admin Link -->
            <div class="bg-blue-50 rounded-lg p-4 mt-6 text-sm">
                <p class="font-semibold text-blue-800 mb-2">Test Credentials:</p>
                <p class="text-blue-700">Customer: john@example.com / password</p>
                <p class="text-blue-700">Admin: admin / password</p>
                <div class="mt-3 pt-3 border-t border-blue-200">
                    <?php if($isAdminLogin): ?>
                        <a href="<?php echo url('login'); ?>" class="text-blue-600 hover:text-blue-800 underline flex items-center">
                            <i class="fas fa-user mr-2"></i> Customer Login
                        </a>
                    <?php else: ?>
                        <a href="<?php echo url('login?admin=true'); ?>" class="text-blue-600 hover:text-blue-800 underline flex items-center">
                            <i class="fas fa-user-shield mr-2"></i> Admin Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

[x-cloak] { display: none !important; }
</style>

<?php include 'includes/footer.php'; ?>