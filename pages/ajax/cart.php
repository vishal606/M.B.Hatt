<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

$response = ['success' => false, 'message' => ''];

switch ($action) {
    case 'add':
        if ($productId > 0) {
            // Verify product exists and is active
            $stmt = $pdo->prepare("SELECT id, status FROM products WHERE id = ? AND status = 'active'");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            if ($product) {
                addToCart($productId, $quantity);
                $response['success'] = true;
                $response['cartCount'] = getCartCount();
                $response['message'] = 'Product added to cart';
            } else {
                $response['message'] = 'Product not found or inactive';
            }
        } else {
            $response['message'] = 'Invalid product ID';
        }
        break;
        
    case 'remove':
        if ($productId > 0) {
            removeFromCart($productId);
            $response['success'] = true;
            $response['cartCount'] = getCartCount();
            $response['message'] = 'Product removed from cart';
        } else {
            $response['message'] = 'Invalid product ID';
        }
        break;
        
    case 'update':
        if ($productId > 0 && $quantity > 0) {
            // Update quantity
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId] = $quantity;
                $response['success'] = true;
                $response['cartCount'] = getCartCount();
                $response['cartTotal'] = getCartTotal();
                $response['message'] = 'Quantity updated';
            } else {
                $response['message'] = 'Product not in cart';
            }
        } else {
            $response['message'] = 'Invalid quantity';
        }
        break;
        
    case 'clear':
        clearCart();
        $response['success'] = true;
        $response['cartCount'] = 0;
        $response['message'] = 'Cart cleared';
        break;
        
    case 'get':
        $response['success'] = true;
        $response['cart'] = getCart();
        $response['cartCount'] = getCartCount();
        $response['cartTotal'] = getCartTotal();
        break;
        
    default:
        $response['message'] = 'Invalid action';
}

echo json_encode($response);
