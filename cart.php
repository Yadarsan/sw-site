<?php
/**
 * AJAX Cart Handler
 * Handles all cart-related AJAX requests
 */

// Prevent any output before JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors in output
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Load bootstrap - adjust path if needed
    $bootstrapPath = '../bootstrap.php';
    if (!file_exists($bootstrapPath)) {
        // Try alternative path
        $bootstrapPath = __DIR__ . '/../bootstrap.php';
        if (!file_exists($bootstrapPath)) {
            throw new Exception('Bootstrap file not found');
        }
    }
    require_once $bootstrapPath;

    // Clean any output that might have been generated
    ob_clean();

    // Set JSON header
    header('Content-Type: application/json');

    // Get request data
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    // Validate request
    if (!$input || !isset($input['action'])) {
        throw new Exception('Invalid request format');
    }

    $action = $input['action'];
    $cart = app()->getShoppingCart();
    $response = ['success' => false];

    switch ($action) {
        case 'add':
            // Add product to cart
            if (!isset($input['product_id']) || !isset($input['quantity'])) {
                throw new Exception('Missing required parameters');
            }
            
            $productId = intval($input['product_id']);
            $quantity = intval($input['quantity']);
            
            if ($productId <= 0 || $quantity <= 0) {
                throw new Exception('Invalid product ID or quantity');
            }
            
            // Check if product exists and has stock
            $product = app()->getProductCatalog()->getProductDetails($productId);
            if (!$product) {
                throw new Exception('Product not found');
            }
            
            // Check current cart quantity
            $cartContents = $cart->getContents();
            $currentQuantityInCart = 0;
            foreach ($cartContents as $item) {
                if ($item['product']['product_id'] == $productId) {
                    $currentQuantityInCart = $item['quantity'];
                    break;
                }
            }
            
            $totalQuantity = $currentQuantityInCart + $quantity;
            if ($product['stock'] < $totalQuantity) {
                throw new Exception('Insufficient stock. Only ' . $product['stock'] . ' items available.');
            }
            
            // Add to cart
            if ($cart->addProduct($productId, $quantity)) {
                $response['success'] = true;
                $response['message'] = 'Product added to cart';
                $response['cart_count'] = $cart->getItemCount();
                $response['subtotal'] = $cart->getSubtotal();
                
                // Save cart to session
                app()->saveCartToSession();
            } else {
                throw new Exception('Failed to add product to cart');
            }
            break;
            
        case 'update':
            // Update product quantity
            if (!isset($input['product_id']) || !isset($input['quantity'])) {
                throw new Exception('Missing required parameters');
            }
            
            $productId = intval($input['product_id']);
            $quantity = intval($input['quantity']);
            
            if ($productId <= 0 || $quantity < 0) {
                throw new Exception('Invalid product ID or quantity');
            }
            
            // Update quantity (0 means remove)
            if ($quantity === 0) {
                $cart->removeProduct($productId);
                $response['message'] = 'Product removed from cart';
            } else {
                if ($cart->updateQuantity($productId, $quantity)) {
                    $response['message'] = 'Cart updated';
                } else {
                    throw new Exception('Failed to update cart. Please check stock availability.');
                }
            }
            
            $response['success'] = true;
            $response['cart_count'] = $cart->getItemCount();
            $response['subtotal'] = $cart->getSubtotal();
            
            // Save cart to session
            app()->saveCartToSession();
            break;
            
        case 'remove':
            // Remove product from cart
            if (!isset($input['product_id'])) {
                throw new Exception('Missing product ID');
            }
            
            $productId = intval($input['product_id']);
            
            if ($cart->removeProduct($productId)) {
                $response['success'] = true;
                $response['message'] = 'Product removed from cart';
                $response['cart_count'] = $cart->getItemCount();
                $response['subtotal'] = $cart->getSubtotal();
                
                // Save cart to session
                app()->saveCartToSession();
            } else {
                throw new Exception('Failed to remove product');
            }
            break;
            
        case 'clear':
            // Clear entire cart
            $cart->clear();
            $response['success'] = true;
            $response['message'] = 'Cart cleared';
            $response['cart_count'] = 0;
            $response['subtotal'] = 0;
            
            // Save cart to session
            app()->saveCartToSession();
            break;
            
        case 'get':
            // Get cart contents
            $response['success'] = true;
            $response['items'] = $cart->getContents();
            $response['cart_count'] = $cart->getItemCount();
            $response['subtotal'] = $cart->getSubtotal();
            break;
            
        default:
            throw new Exception('Invalid action: ' . $action);
    }
    
} catch (Exception $e) {
    // Clean any output
    ob_clean();
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    // Log the error
    error_log('AJAX Cart Error: ' . $e->getMessage());
    
    // Set appropriate HTTP status
    http_response_code(400);
}

// Clean any remaining output
ob_end_clean();

// Ensure we only output JSON
echo json_encode($response);
exit;
?>