-- Update categories table to support subcategories and icons
ALTER TABLE categories ADD COLUMN parent_id INT DEFAULT NULL AFTER id;
ALTER TABLE categories ADD COLUMN icon VARCHAR(50) DEFAULT NULL AFTER slug;
ALTER TABLE categories ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER icon;
ALTER TABLE categories ADD CONSTRAINT fk_parent_category FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL;

-- Clear old categories
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE categories;
SET FOREIGN_KEY_CHECKS = 1;

-- Add Main Categories and Subcategories
-- 1. Audio Devices
INSERT INTO categories (name, slug, icon, status) VALUES ('Audio Devices', 'audio-devices', '🎧', 'active');
SET @audio_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@audio_id, 'Wireless Earbuds', 'wireless-earbuds', 'active'),
(@audio_id, 'Neckband Earphones', 'neckband-earphones', 'active'),
(@audio_id, 'Bluetooth Speakers', 'bluetooth-speakers', 'active'),
(@audio_id, 'Mini Speakers', 'mini-speakers', 'active'),
(@audio_id, 'Soundbars', 'soundbars', 'active'),
(@audio_id, 'Headphones', 'headphones', 'active');

-- 2. Smart Lights & Lighting
INSERT INTO categories (name, slug, icon, status) VALUES ('Smart Lights & Lighting', 'smart-lighting', '💡', 'active');
SET @light_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@light_id, 'LED Strip Lights', 'led-strip-lights', 'active'),
(@light_id, 'Smart Bulbs', 'smart-bulbs', 'active'),
(@light_id, 'Ring Lights', 'ring-lights', 'active'),
(@light_id, 'Desk Lamps', 'desk-lamps', 'active'),
(@light_id, 'Decorative Lights', 'decorative-lights', 'active'),
(@light_id, 'Night Lamps', 'night-lamps', 'active');

-- 3. Electronic Accessories
INSERT INTO categories (name, slug, icon, status) VALUES ('Electronic Accessories', 'electronic-accessories', '🔌', 'active');
SET @elec_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@elec_id, 'Chargers & Adapters', 'chargers-adapters', 'active'),
(@elec_id, 'Power Banks', 'power-banks', 'active'),
(@elec_id, 'Cables', 'cables', 'active'),
(@elec_id, 'USB Hubs', 'usb-hubs', 'active'),
(@elec_id, 'Extension Boards', 'extension-boards', 'active');

-- 4. Mobile & Gadget Accessories
INSERT INTO categories (name, slug, icon, status) VALUES ('Mobile & Gadget Accessories', 'mobile-gadget-accessories', '📱', 'active');
SET @mobile_acc_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@mobile_acc_id, 'Phone Holders / Stands', 'phone-holders-stands', 'active'),
(@mobile_acc_id, 'Tripods', 'tripods', 'active'),
(@mobile_acc_id, 'Selfie Sticks', 'selfie-sticks', 'active'),
(@mobile_acc_id, 'Cooling Fans', 'cooling-fans', 'active'),
(@mobile_acc_id, 'Screen Cleaners', 'screen-cleaners', 'active');

-- 5. Smart Gadgets (China Gadgets)
INSERT INTO categories (name, slug, icon, status) VALUES ('Smart Gadgets', 'smart-gadgets', '🧠', 'active');
SET @smart_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@smart_id, 'Smart Watches', 'smart-watches', 'active'),
(@smart_id, 'Fitness Bands', 'fitness-bands', 'active'),
(@smart_id, 'Mini Gadgets', 'mini-gadgets', 'active'),
(@smart_id, 'USB Gadgets', 'usb-gadgets', 'active'),
(@smart_id, 'Car Gadgets', 'car-gadgets', 'active');

-- 6. Canvas & Wall Art
INSERT INTO categories (name, slug, icon, status) VALUES ('Canvas & Wall Art', 'canvas-wall-art', '🖼️', 'active');
SET @art_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@art_id, 'Printed Canvas', 'printed-canvas', 'active'),
(@art_id, 'Custom Canvas Print', 'custom-canvas-print', 'active'),
(@art_id, 'Islamic Wall Art', 'islamic-wall-art', 'active'),
(@art_id, 'Motivational Posters', 'motivational-posters', 'active'),
(@art_id, 'Photo Frames', 'photo-frames', 'active');

-- 7. Gift & Combo Offers
INSERT INTO categories (name, slug, icon, status) VALUES ('Gift & Combo Offers', 'gift-combo-offers', '🎁', 'active');
SET @gift_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@gift_id, 'Gift Boxes', 'gift-boxes', 'active'),
(@gift_id, 'Combo Deals', 'combo-deals', 'active'),
(@gift_id, 'Festival Offers', 'festival-offers', 'active'),
(@gift_id, 'Corporate Gifts', 'corporate-gifts', 'active');

-- 8. Offers & Deals
INSERT INTO categories (name, slug, icon, status) VALUES ('Offers & Deals', 'offers-deals', '🔥', 'active');
SET @offers_id = LAST_INSERT_ID();
INSERT INTO categories (parent_id, name, slug, status) VALUES 
(@offers_id, 'Flash Sale', 'flash-sale', 'active'),
(@offers_id, 'Discounted Products', 'discounted-products', 'active'),
(@offers_id, 'Clearance Sale', 'clearance-sale', 'active');
