USE db_hotel;

-- Insert sample hotels
INSERT INTO tb_hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES
-- Rabat
('Sofitel Rabat Jardin des Roses', 'BP 450 Souissi, Rabat', 'Hôtel de luxe avec jardin andalou, restaurants gastronomiques et spa.', 'contact@sofitel-rabat.com', '+212 537-675656', 'www.sofitel-rabat.com'),
('Hotel Diwan Rabat', 'Place de l''Unité Africaine, Rabat', 'Situé au cœur de la ville, proche des monuments historiques.', 'contact@diwanrabat.com', '+212 537-262727', 'www.diwanrabat.com'),
('Riad Dar El Kebira', 'medina, Rabat', 'Riad traditionnel avec architecture authentique.', 'info@darelkebira.com', '+212 537-724906', 'www.riaddarelkebira.com'),

-- Casablanca
('Four Seasons Casablanca', 'Boulevard de la Corniche, Casablanca', 'Hôtel moderne avec vue sur l''océan.', 'info@fscasablanca.com', '+212 529-073700', 'www.fourseasons.com/casablanca'),
('Hyatt Regency Casablanca', 'Place des Nations Unies, Casablanca', 'Au cœur du quartier d''affaires.', 'casablanca.regency@hyatt.com', '+212 522-431234', 'www.hyatt.com'),
('Movenpick Hotel Casablanca', 'Rond-point Hassan II, Casablanca', 'Vue panoramique sur la ville.', 'hotel.casablanca@movenpick.com', '+212 522-520520', 'www.movenpick.com'),

-- Marrakech
('La Mamounia', 'Avenue Bab Jdid, Marrakech', 'Palace historique avec jardins luxuriants.', 'info@mamounia.com', '+212 524-388600', 'www.mamounia.com'),
('Royal Mansour Marrakech', 'Rue Abou Abbas El Sebti, Marrakech', 'Riads privés de luxe.', 'contact@royalmansour.ma', '+212 529-808080', 'www.royalmansour.com'),
('Four Seasons Resort Marrakech', 'Avenue de la Menara, Marrakech', 'Oasis moderne avec spa.', 'info@fsmarrakech.com', '+212 524-359200', 'www.fourseasons.com/marrakech'),

-- Fès
('Riad Fès', 'Derb Ben Slimane, Fès', 'Riad de luxe dans la médina.', 'contact@riadfes.com', '+212 535-947610', 'www.riadfes.com'),
('Palais Faraj Suites & Spa', 'Bab Ziat, Fès', 'Vue panoramique sur la médina.', 'info@palaisfaraj.com', '+212 535-635356', 'www.palaisfaraj.com'),
('Hotel Sahrai', 'Bab Lghoul, Fès', 'Design contemporain avec vue sur la médina.', 'info@hotelsahrai.com', '+212 535-940332', 'www.hotelsahrai.com'),

-- Tanger
('El Minzah Hotel', 'Rue de la Liberté, Tanger', 'Hôtel historique au style colonial.', 'reservation@elminzah.com', '+212 539-333444', 'www.elminzah.com'),
('Mövenpick Hotel & Casino Malabata', 'Route de Malabata, Tanger', 'Vue sur le détroit de Gibraltar.', 'hotel.tanger@movenpick.com', '+212 539-934934', 'www.movenpick.com'),
('Hilton Tanger City Center', 'Place du Maghreb, Tanger', 'Au cœur du nouveau centre-ville.', 'tanger.info@hilton.com', '+212 539-309999', 'www.hilton.com'),

-- Agadir
('Sofitel Agadir Royal Bay Resort', 'Baie des Palmiers, Agadir', 'Resort de luxe en bord de mer.', 'H5707@sofitel.com', '+212 528-849999', 'www.sofitel-agadir.com'),
('Hyatt Place Taghazout Bay', 'Taghazout Bay, Agadir', 'Vue sur l''océan et les montagnes.', 'agadir.place@hyatt.com', '+212 528-876767', 'www.hyatt.com'),
('Atlas Amadil Beach', 'Boulevard 20 Août, Agadir', 'Accès direct à la plage.', 'contact@amadilbeach.com', '+212 528-847020', 'www.amadilbeach.com'),

-- Insert popular hotels
INSERT INTO tb_hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES
('La Mamounia Marrakech', 'Avenue Bab Jdid, 40040 Marrakech, Maroc', 
'Un palace légendaire offrant un service impeccable et un luxe raffiné au cœur de Marrakech.', 
'contact@mamounia.com', '+212 524 388 600', 'www.mamounia.com'),

('Royal Mansour Marrakech', 'Rue Abou Abbas El Sebti, 40000 Marrakech, Maroc',
'Une expérience unique de luxe marocain authentique avec des riads privés.', 
'contact@royalmansour.ma', '+212 529 808 080', 'www.royalmansour.com'),

('Four Seasons Hotel Casablanca', 'Boulevard de la Corniche, 20050 Casablanca, Maroc',
'Un hôtel moderne de luxe surplombant l''océan Atlantique.', 
'reservations.cas@fourseasons.com', '+212 529 073 700', 'www.fourseasons.com/casablanca'),

('Mandarin Oriental Marrakech', 'Route du Golf Royal, 40000 Marrakech, Maroc',
'Un resort contemporain luxueux avec des villas privées et des suites spacieuses.', 
'momrk-reservations@mohg.com', '+212 524 298 888', 'www.mandarinoriental.com/marrakech');

-- Insert rooms for each hotel
INSERT INTO tb_chambres (id_hotel, type_chambre, prix, disponibilite, nombre_lits)
SELECT 
    h.id_hotel,
    t.type_chambre,
    CASE 
        WHEN t.type_chambre = 'simple' THEN 800 + RAND() * 400
        WHEN t.type_chambre = 'double' THEN 1200 + RAND() * 600
        WHEN t.type_chambre = 'suite' THEN 2000 + RAND() * 1000
    END as prix,
    1 as disponibilite,
    CASE 
        WHEN t.type_chambre = 'simple' THEN 1
        WHEN t.type_chambre = 'double' THEN 2
        ELSE 3
    END as nombre_lits
FROM tb_hotels h
CROSS JOIN (
    SELECT 'simple' as type_chambre
    UNION SELECT 'double'
    UNION SELECT 'suite'
) t;

-- Insert rooms for popular hotels
INSERT INTO tb_chambres (id_hotel, type_chambre, prix, nombre_lits) 
SELECT 
    h.id_hotel,
    tc.type_chambre,
    CASE 
        WHEN tc.type_chambre = 'simple' THEN 2500
        WHEN tc.type_chambre = 'double' THEN 3500
        WHEN tc.type_chambre = 'suite' THEN 5000
    END as prix,
    CASE 
        WHEN tc.type_chambre = 'simple' THEN 1
        WHEN tc.type_chambre = 'double' THEN 2
        WHEN tc.type_chambre = 'suite' THEN 3
    END as nombre_lits
FROM tb_hotels h
CROSS JOIN (
    SELECT 'simple' as type_chambre
    UNION SELECT 'double'
    UNION SELECT 'suite'
) tc
WHERE h.nom_hotel IN (
    'La Mamounia Marrakech',
    'Royal Mansour Marrakech',
    'Four Seasons Hotel Casablanca',
    'Mandarin Oriental Marrakech'
);

-- Insert sample admin
INSERT INTO tb_admin (username, email, password) VALUES
('admin', 'admin@hotel.com', '$2y$10$8jxMJYBxMfxz3qH5.ClBZOYqkWZ4kqX5GYwOKsX0DQ7GycXpLyde6');  -- Password: admin123

-- Insert sample client (if not exists)
INSERT IGNORE INTO tb_clients (nom, email, password, adresse, telephone) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Test Street', '+212 6123-45678'); -- Password: password123
