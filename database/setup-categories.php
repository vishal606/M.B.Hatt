<?php
/**
 * MBHaat.com - Setup Categories and Tags
 * Run this script to insert all categories with subcategories and tags
 */

require_once '../includes/config.php';

echo "<style>
body { font-family: Arial; padding: 20px; max-width: 900px; margin: 0 auto; background: #f5f5f5; }
.box { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h2 { color: #36064D; margin-top: 0; }
h3 { color: #76D2DB; }
.success { color: green; }
.error { color: red; }
pre { background: #f4f4f4; padding: 10px; overflow-x: auto; font-size: 11px; }
button { padding: 12px 24px; background: #36064D; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
</style>";

echo "<div class='box'><h2>🛍️ MBHaat.com Category Setup</h2></div>";

if (!isset($_POST['setup'])) {
    echo "<div class='box'>";
    echo "<p>This will create all categories, subcategories, and tags for your store.</p>";
    echo "<form method='post'><button type='submit' name='setup'>🚀 SETUP NOW</button></form>";
    echo "</div>";
    exit;
}

// Define Main Categories
$categories = [
    [
        'name' => '🎧 Audio Devices',
        'slug' => 'audio-devices',
        'icon' => 'fa-headphones',
        'description' => 'Focus on your AirPods and speakers',
        'subcategories' => [
            ['name' => 'Wireless Earbuds', 'slug' => 'wireless-earbuds', 'icon' => 'fa-headphones'],
            ['name' => 'Neckband Earphones', 'slug' => 'neckband-earphones', 'icon' => 'fa-headphones'],
            ['name' => 'Bluetooth Speakers', 'slug' => 'bluetooth-speakers', 'icon' => 'fa-volume-up'],
            ['name' => 'Mini Speakers', 'slug' => 'mini-speakers', 'icon' => 'fa-volume-down'],
            ['name' => 'Soundbars', 'slug' => 'soundbars', 'icon' => 'fa-music'],
            ['name' => 'Headphones', 'slug' => 'headphones', 'icon' => 'fa-headphones-alt'],
        ]
    ],
    [
        'name' => '💡 Smart Lights & Lighting',
        'slug' => 'smart-lights-lighting',
        'icon' => 'fa-lightbulb',
        'description' => 'Covers all lighting products',
        'subcategories' => [
            ['name' => 'LED Strip Lights', 'slug' => 'led-strip-lights', 'icon' => 'fa-lightbulb'],
            ['name' => 'Smart Bulbs', 'slug' => 'smart-bulbs', 'icon' => 'fa-lightbulb'],
            ['name' => 'Ring Lights', 'slug' => 'ring-lights', 'icon' => 'fa-circle'],
            ['name' => 'Desk Lamps', 'slug' => 'desk-lamps', 'icon' => 'fa-lamp'],
            ['name' => 'Decorative Lights', 'slug' => 'decorative-lights', 'icon' => 'fa-star'],
            ['name' => 'Night Lamps', 'slug' => 'night-lamps', 'icon' => 'fa-moon'],
        ]
    ],
    [
        'name' => '🔌 Electronic Accessories',
        'slug' => 'electronic-accessories',
        'icon' => 'fa-plug',
        'description' => 'High-demand, fast-selling items',
        'subcategories' => [
            ['name' => 'Chargers & Adapters', 'slug' => 'chargers-adapters', 'icon' => 'fa-bolt'],
            ['name' => 'Power Banks', 'slug' => 'power-banks', 'icon' => 'fa-battery-full'],
            ['name' => 'Cables', 'slug' => 'cables', 'icon' => 'fa-usb'],
            ['name' => 'USB Hubs', 'slug' => 'usb-hubs', 'icon' => 'fa-usb'],
            ['name' => 'Extension Boards', 'slug' => 'extension-boards', 'icon' => 'fa-plug'],
        ]
    ],
    [
        'name' => '📱 Mobile & Gadget Accessories',
        'slug' => 'mobile-gadget-accessories',
        'icon' => 'fa-mobile-alt',
        'description' => 'Support products for phones and devices',
        'subcategories' => [
            ['name' => 'Phone Holders & Stands', 'slug' => 'phone-holders-stands', 'icon' => 'fa-mobile'],
            ['name' => 'Tripods', 'slug' => 'tripods', 'icon' => 'fa-camera'],
            ['name' => 'Selfie Sticks', 'slug' => 'selfie-sticks', 'icon' => 'fa-mobile-alt'],
            ['name' => 'Cooling Fans', 'slug' => 'cooling-fans', 'icon' => 'fa-fan'],
            ['name' => 'Screen Cleaners', 'slug' => 'screen-cleaners', 'icon' => 'fa-spray-can'],
        ]
    ],
    [
        'name' => '🧠 Smart Gadgets',
        'slug' => 'smart-gadgets',
        'icon' => 'fa-microchip',
        'description' => 'Trending tech and China gadgets',
        'subcategories' => [
            ['name' => 'Smart Watches', 'slug' => 'smart-watches', 'icon' => 'fa-clock'],
            ['name' => 'Fitness Bands', 'slug' => 'fitness-bands', 'icon' => 'fa-heartbeat'],
            ['name' => 'Mini Gadgets', 'slug' => 'mini-gadgets', 'icon' => 'fa-tools'],
            ['name' => 'USB Gadgets', 'slug' => 'usb-gadgets', 'icon' => 'fa-usb'],
            ['name' => 'Car Gadgets', 'slug' => 'car-gadgets', 'icon' => 'fa-car'],
        ]
    ],
    [
        'name' => '🖼️ Canvas & Wall Art',
        'slug' => 'canvas-wall-art',
        'icon' => 'fa-image',
        'description' => 'Non-electronic segment',
        'subcategories' => [
            ['name' => 'Printed Canvas', 'slug' => 'printed-canvas', 'icon' => 'fa-image'],
            ['name' => 'Custom Canvas Print', 'slug' => 'custom-canvas-print', 'icon' => 'fa-palette'],
            ['name' => 'Islamic Wall Art', 'slug' => 'islamic-wall-art', 'icon' => 'fa-star-and-crescent'],
            ['name' => 'Motivational Posters', 'slug' => 'motivational-posters', 'icon' => 'fa-quote-right'],
            ['name' => 'Photo Frames', 'slug' => 'photo-frames', 'icon' => 'fa-frame'],
        ]
    ],
    [
        'name' => '🎁 Gift & Combo Offers',
        'slug' => 'gift-combo-offers',
        'icon' => 'fa-gift',
        'description' => 'Important for sales growth',
        'subcategories' => [
            ['name' => 'Gift Boxes', 'slug' => 'gift-boxes', 'icon' => 'fa-box'],
            ['name' => 'Combo Deals', 'slug' => 'combo-deals', 'icon' => 'fa-boxes'],
            ['name' => 'Festival Offers', 'slug' => 'festival-offers', 'icon' => 'fa-calendar-star'],
            ['name' => 'Corporate Gifts', 'slug' => 'corporate-gifts', 'icon' => 'fa-briefcase'],
        ]
    ],
    [
        'name' => '🔥 Offers & Deals',
        'slug' => 'offers-deals',
        'icon' => 'fa-fire',
        'description' => 'Dynamic category - system generated',
        'subcategories' => [
            ['name' => 'Flash Sale', 'slug' => 'flash-sale', 'icon' => 'fa-bolt'],
            ['name' => 'Discounted Products', 'slug' => 'discounted-products', 'icon' => 'fa-tags'],
            ['name' => 'Clearance Sale', 'slug' => 'clearance-sale', 'icon' => 'fa-percent'],
        ]
    ],
];

// Insert Categories
$inserted = 0;
echo "<div class='box'><h3>📁 Creating Categories</h3>";

foreach ($categories as $cat) {
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $check->execute([$cat['slug']]);
    
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, icon, sort_order, status) VALUES (?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$cat['name'], $cat['slug'], $cat['description'], $cat['icon'], $inserted]);
        $parentId = $pdo->lastInsertId();
        $inserted++;
        
        echo "<p class='success'>✓ {$cat['name']}</p>";
        
        // Insert subcategories
        foreach ($cat['subcategories'] as $i => $sub) {
            $subCheck = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
            $subCheck->execute([$sub['slug']]);
            
            if (!$subCheck->fetch()) {
                $subStmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, parent_id, sort_order, status) VALUES (?, ?, ?, ?, ?, 'active')");
                $subStmt->execute([$sub['name'], $sub['slug'], $sub['icon'], $parentId, $i]);
                echo "<p style='margin-left: 20px; color: #666;'>└─ {$sub['name']}</p>";
            }
        }
    } else {
        echo "<p>{$cat['name']} (already exists)</p>";
    }
}
echo "</div>";

// Define Tags
$tags = [
    ['name' => 'Trending', 'slug' => 'trending', 'description' => 'Popular right now'],
    ['name' => 'Best Seller', 'slug' => 'best-seller', 'description' => 'Top selling products'],
    ['name' => 'New Arrival', 'slug' => 'new-arrival', 'description' => 'Just added'],
    ['name' => 'Flash Sale', 'slug' => 'flash-sale', 'description' => 'Limited time offer'],
    ['name' => 'Hot Deal', 'slug' => 'hot-deal', 'description' => 'Great discounts'],
    ['name' => 'Premium', 'slug' => 'premium', 'description' => 'High quality products'],
    ['name' => 'Budget Friendly', 'slug' => 'budget-friendly', 'description' => 'Affordable options'],
    ['name' => 'Top Rated', 'slug' => 'top-rated', 'description' => 'Highly rated by customers'],
];

// Insert Tags
$tagInserted = 0;
echo "<div class='box'><h3>🏷️ Creating Tags</h3>";

foreach ($tags as $tag) {
    $check = $pdo->prepare("SELECT id FROM tags WHERE slug = ?");
    $check->execute([$tag['slug']]);
    
    if (!$check->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO tags (name, slug, description, status) VALUES (?, ?, ?, 'active')");
        $stmt->execute([$tag['name'], $tag['slug'], $tag['description']]);
        $tagInserted++;
        echo "<p class='success'>✓ {$tag['name']}</p>";
    } else {
        echo "<p>{$tag['name']} (already exists)</p>";
    }
}
echo "</div>";

// Summary
echo "<div class='box' style='background: #d4edda;'>";
echo "<h3 class='success'>✓ Setup Complete!</h3>";
echo "<p><strong>$inserted</strong> main categories created</p>";
echo "<p><strong>$tagInserted</strong> tags created</p>";
echo "<a href='../admin/categories.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#36064D;color:white;text-decoration:none;border-radius:5px;'>View Categories in Admin</a>";
echo "</div>";

echo "<div class='box' style='background: #fff3cd;'>";
echo "<p style='color:red'><strong>⚠ DELETE THIS FILE AFTER USE!</strong></p>";
echo "<form method='post'><button type='submit' name='delete'>🗑️ Delete setup-categories.php</button></form>";
if (isset($_POST['delete'])) {
    unlink(__FILE__);
    echo "<script>alert('File deleted!');</script>";
}
echo "</div>";
?>
