<?php
require_once __DIR__ . '/src/init.php';

echo "<h2>MBHaat Dummy Data Seeder</h2>";

try {
    // 1. Add some dummy products
    $categories = Database::fetchAll("SELECT id FROM categories");
    if (empty($categories)) {
        echo "Error: No categories found. Please run schema.sql first.<br>";
        exit;
    }

    $dummyProducts = [
        [
            'title' => 'Digital Marketing Masterclass',
            'description' => 'A complete guide to digital marketing in 2026. Includes SEO, SEM, and Social Media strategies.',
            'price' => 1500.00,
            'category_id' => $categories[3]['id'] ?? 1, // Courses
            'status' => 'active'
        ],
        [
            'title' => 'Modern E-commerce UI Kit',
            'description' => 'Beautifully crafted Figma templates for your next online store project.',
            'price' => 2500.00,
            'category_id' => $categories[1]['id'] ?? 1, // Templates
            'status' => 'active'
        ],
        [
            'title' => 'Laravel SaaS Boilerplate',
            'description' => 'Save 100+ hours with this production-ready SaaS starter kit built with Laravel and Tailwind.',
            'price' => 4500.00,
            'category_id' => $categories[2]['id'] ?? 1, // Software
            'status' => 'active'
        ],
        [
            'title' => 'High-Conversion Landing Page E-book',
            'description' => 'Learn the psychology behind landing pages that convert like crazy.',
            'price' => 500.00,
            'category_id' => $categories[0]['id'] ?? 1, // E-books
            'status' => 'active'
        ],
        [
            'title' => '3D Abstract Icon Pack',
            'description' => '50+ high-quality 3D icons for modern web and mobile designs.',
            'price' => 1200.00,
            'category_id' => $categories[4]['id'] ?? 1, // Graphics
            'status' => 'active'
        ]
    ];

    foreach ($dummyProducts as $p) {
        $slug = makeSlug($p['title']);
        $check = Database::fetch("SELECT id FROM products WHERE slug = ?", [$slug]);
        if (!$check) {
            $productId = Database::insert(
                "INSERT INTO products (category_id, title, slug, description, price, status) VALUES (?, ?, ?, ?, ?, ?)",
                [$p['category_id'], $p['title'], $slug, $p['description'], $p['price'], $p['status']]
            );
            echo "Added product: {$p['title']} (ID: $productId)<br>";
        } else {
            echo "Product already exists: {$p['title']}<br>";
        }
    }

    echo "<br><b>Seeding completed!</b><br>";
    echo "<a href='index.php'>Go to Home</a> | <a href='admin/'>Go to Admin</a>";

} catch (Exception $e) {
    echo "Error seeding data: " . $e->getMessage();
}
