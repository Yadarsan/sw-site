<?php
// Set page title
$pageTitle = '404 - Page Not Found';

// Include header
include 'includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-2xl w-full text-center">
        <!-- 404 Illustration -->
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-primary-600 animate-pulse">404</h1>
            <div class="mt-4">
                <i class="fas fa-exclamation-triangle text-6xl text-yellow-500"></i>
            </div>
        </div>
        
        <!-- Error Message -->
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Oops! Page Not Found</h2>
        <p class="text-xl text-gray-600 mb-8">
            The page you're looking for doesn't exist or has been moved.
        </p>
        
        <!-- Search Box -->
        <div class="max-w-md mx-auto mb-8">
            <form action="<?php echo url('search'); ?>" method="GET" class="flex">
                <input type="text" 
                       name="q" 
                       placeholder="Search for products..." 
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <button type="submit" class="bg-primary-600 text-white px-6 py-3 rounded-r-lg hover:bg-primary-700 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
            <a href="<?php echo url(); ?>" class="bg-primary-600 text-white px-8 py-3 rounded-lg hover:bg-primary-700 transition duration-300 inline-flex items-center justify-center">
                <i class="fas fa-home mr-2"></i> Go to Homepage
            </a>
            <button onclick="history.back()" class="bg-white text-gray-700 border-2 border-gray-300 px-8 py-3 rounded-lg hover:bg-gray-100 transition duration-300 inline-flex items-center justify-center">
                <i class="fas fa-arrow-left mr-2"></i> Go Back
            </button>
        </div>
        
        <!-- Helpful Links -->
        <div class="border-t pt-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Here are some helpful links:</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <a href="<?php echo url('products'); ?>" class="text-primary-600 hover:text-primary-700 hover:underline">
                    <i class="fas fa-shopping-bag mr-1"></i> All Products
                </a>
                <a href="<?php echo url('deals'); ?>" class="text-primary-600 hover:text-primary-700 hover:underline">
                    <i class="fas fa-tags mr-1"></i> Special Offers
                </a>
                <a href="<?php echo url('contact'); ?>" class="text-primary-600 hover:text-primary-700 hover:underline">
                    <i class="fas fa-envelope mr-1"></i> Contact Us
                </a>
                <a href="<?php echo url('faq'); ?>" class="text-primary-600 hover:text-primary-700 hover:underline">
                    <i class="fas fa-question-circle mr-1"></i> FAQs
                </a>
            </div>
        </div>
        
        <!-- Customer Support -->
        <div class="mt-12 bg-blue-50 rounded-lg p-6">
            <p class="text-gray-700">
                <i class="fas fa-headset text-blue-600 mr-2"></i>
                Need help? Contact our customer support at 
                <a href="tel:1234567890" class="text-blue-600 hover:underline font-semibold">(123) 456-7890</a> 
                or 
                <a href="mailto:support@aweelectronics.com" class="text-blue-600 hover:underline font-semibold">support@aweelectronics.com</a>
            </p>
        </div>
    </div>
</div>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-float {
    animation: float 3s ease-in-out infinite;
}
</style>

<?php include 'includes/footer.php'; ?>