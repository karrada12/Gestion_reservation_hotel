<?php
require_once "../includes/config.php";

// Array of hotels to insert
$hotels = [
    // Marrakech
    [
        'nom_hotel' => 'La Mamounia Palace',
        'adresse' => 'Avenue Bab Jdid, Marrakech',
        'description' => 'Palace légendaire avec jardins luxuriants et spa.',
        'email' => 'contact@mamounia.com',
        'telephone' => '+212 524-388-600',
        'site_web' => 'www.mamounia.com'
    ],
    [
        'nom_hotel' => 'Royal Mansour Marrakech',
        'adresse' => 'Rue Abou Abbas El Sebti, Marrakech',
        'description' => 'Riads privés de luxe avec service personnalisé.',
        'email' => 'info@royalmansour.ma',
        'telephone' => '+212 529-808-080',
        'site_web' => 'www.royalmansour.com'
    ],
    [
        'nom_hotel' => 'Four Seasons Resort Marrakech',
        'adresse' => 'Boulevard de la Menara, Marrakech',
        'description' => 'Resort moderne avec spa et piscines.',
        'email' => 'info@fsmarrakech.com',
        'telephone' => '+212 524-359-200',
        'site_web' => 'www.fourseasons.com/marrakech'
    ],
    [
        'nom_hotel' => 'Mandarin Oriental Marrakech',
        'adresse' => 'Route du Golf Royal, Marrakech',
        'description' => 'Villas luxueuses avec jardins privés.',
        'email' => 'momrk@mohg.com',
        'telephone' => '+212 524-298-888',
        'site_web' => 'www.mandarinoriental.com/marrakech'
    ],
    [
        'nom_hotel' => 'Fairmont Royal Palm Marrakech',
        'adresse' => 'Route d\'Amizmiz, Marrakech',
        'description' => 'Resort de golf avec vue sur l\'Atlas.',
        'email' => 'palm@fairmont.com',
        'telephone' => '+212 524-487-800',
        'site_web' => 'www.fairmont.com/marrakech'
    ],
    
    // Casablanca
    [
        'nom_hotel' => 'Four Seasons Casablanca',
        'adresse' => 'Boulevard de la Corniche, Casablanca',
        'description' => 'Élégance moderne face à l\'océan.',
        'email' => 'info@fscasablanca.com',
        'telephone' => '+212 529-073-700',
        'site_web' => 'www.fourseasons.com/casablanca'
    ],
    [
        'nom_hotel' => 'Sofitel Casablanca Tour Blanche',
        'adresse' => 'Rue Sidi Belyout, Casablanca',
        'description' => 'Au cœur du quartier des affaires.',
        'email' => 'contact@sofitel-casablanca.com',
        'telephone' => '+212 522-456-200',
        'site_web' => 'www.sofitel.com/casablanca'
    ],
    [
        'nom_hotel' => 'Hyatt Regency Casablanca',
        'adresse' => 'Place des Nations Unies, Casablanca',
        'description' => 'Vue panoramique sur la ville.',
        'email' => 'casablanca.regency@hyatt.com',
        'telephone' => '+212 522-431-234',
        'site_web' => 'www.hyatt.com/casablanca'
    ],
    [
        'nom_hotel' => 'Mövenpick Hotel Casablanca',
        'adresse' => 'Round Point Hassan II, Casablanca',
        'description' => 'Style contemporain et confort.',
        'email' => 'hotel.casablanca@movenpick.com',
        'telephone' => '+212 522-520-520',
        'site_web' => 'www.movenpick.com/casablanca'
    ],
    
    // Agadir
    [
        'nom_hotel' => 'Sofitel Agadir Royal Bay',
        'adresse' => 'Baie des Palmiers, Agadir',
        'description' => 'Resort de luxe en bord de mer.',
        'email' => 'contact@sofitel-agadir.com',
        'telephone' => '+212 528-849-000',
        'site_web' => 'www.sofitel.com/agadir'
    ],
    [
        'nom_hotel' => 'Hyatt Place Taghazout Bay',
        'adresse' => 'Taghazout Bay, Agadir',
        'description' => 'Paradis des surfeurs avec spa.',
        'email' => 'info@hyattplace-taghazout.com',
        'telephone' => '+212 528-876-767',
        'site_web' => 'www.hyatt.com/taghazout'
    ],
    [
        'nom_hotel' => 'Riu Palace Tikida Agadir',
        'adresse' => 'Chemin des Dunes, Agadir',
        'description' => 'All-inclusive de luxe.',
        'email' => 'palace.tikida@riu.com',
        'telephone' => '+212 528-847-300',
        'site_web' => 'www.riu.com/agadir'
    ],
    
    // Tanger
    [
        'nom_hotel' => 'Hilton Tanger City Center',
        'adresse' => 'Place du Maghreb Arabe, Tanger',
        'description' => 'Vue sur le détroit de Gibraltar.',
        'email' => 'info@hiltontanger.com',
        'telephone' => '+212 539-309-500',
        'site_web' => 'www.hilton.com/tanger'
    ],
    [
        'nom_hotel' => 'El Minzah Hotel',
        'adresse' => 'Rue de la Liberté, Tanger',
        'description' => 'Charme colonial et histoire.',
        'email' => 'contact@elminzah.com',
        'telephone' => '+212 539-333-444',
        'site_web' => 'www.elminzah.com'
    ],
    
    // Fès
    [
        'nom_hotel' => 'Hotel Sahrai',
        'adresse' => 'Dhar El Mehraz, Fès',
        'description' => 'Design contemporain, vue médina.',
        'email' => 'info@hotelsahrai.com',
        'telephone' => '+212 535-940-332',
        'site_web' => 'www.hotelsahrai.com'
    ],
    [
        'nom_hotel' => 'Palais Faraj Suites & Spa',
        'adresse' => 'Bab Ziat, Fès',
        'description' => 'Palace avec vue panoramique.',
        'email' => 'contact@palaisfaraj.com',
        'telephone' => '+212 535-635-356',
        'site_web' => 'www.palaisfaraj.com'
    ],
    
    // Rabat
    [
        'nom_hotel' => 'Sofitel Rabat Jardin des Roses',
        'adresse' => 'BP 450 Souissi, Rabat',
        'description' => 'Élégance française, jardin royal.',
        'email' => 'contact@sofitel-rabat.com',
        'telephone' => '+212 537-675-656',
        'site_web' => 'www.sofitel.com/rabat'
    ],
    [
        'nom_hotel' => 'The View Hotel',
        'adresse' => 'Avenue Annakhil, Rabat',
        'description' => 'Vue sur la vallée du Bouregreg.',
        'email' => 'contact@viewhotel-rabat.com',
        'telephone' => '+212 537-566-566',
        'site_web' => 'www.theviewhotel-rabat.com'
    ]
];

// Prepare the SQL statement
$sql = "INSERT INTO tb_hotels (nom_hotel, adresse, description, email, telephone, site_web) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);

// Insert each hotel
$success_count = 0;
foreach ($hotels as $hotel) {
    mysqli_stmt_bind_param($stmt, "ssssss", 
        $hotel['nom_hotel'],
        $hotel['adresse'],
        $hotel['description'],
        $hotel['email'],
        $hotel['telephone'],
        $hotel['site_web']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $success_count++;
        echo "Hôtel ajouté avec succès : " . $hotel['nom_hotel'] . "<br>";
    } else {
        echo "Erreur lors de l'ajout de l'hôtel " . $hotel['nom_hotel'] . ": " . mysqli_error($conn) . "<br>";
    }
}

echo "<br>Total des hôtels ajoutés : " . $success_count . " sur " . count($hotels);

// Add rooms for each hotel
$room_types = [
    ['type_chambre' => 'Chambre Standard', 'prix' => 800, 'nombre_lits' => 1],
    ['type_chambre' => 'Chambre Deluxe', 'prix' => 1200, 'nombre_lits' => 2],
    ['type_chambre' => 'Suite Junior', 'prix' => 2000, 'nombre_lits' => 2],
    ['type_chambre' => 'Suite Executive', 'prix' => 3500, 'nombre_lits' => 2],
    ['type_chambre' => 'Suite Royale', 'prix' => 5000, 'nombre_lits' => 3],
    ['type_chambre' => 'Suite Présidentielle', 'prix' => 8000, 'nombre_lits' => 4],
    ['type_chambre' => 'Villa Privée', 'prix' => 12000, 'nombre_lits' => 4]
];

$sql = "SELECT id_hotel FROM tb_hotels";
$result = mysqli_query($conn, $sql);

$room_sql = "INSERT INTO tb_chambres (id_hotel, type_chambre, prix, nombre_lits) VALUES (?, ?, ?, ?)";
$room_stmt = mysqli_prepare($conn, $room_sql);

while ($row = mysqli_fetch_assoc($result)) {
    foreach ($room_types as $room) {
        mysqli_stmt_bind_param($room_stmt, "isdi", 
            $row['id_hotel'],
            $room['type_chambre'],
            $room['prix'],
            $room['nombre_lits']
        );
        
        if (mysqli_stmt_execute($room_stmt)) {
            echo "Chambre ajoutée pour l'hôtel ID " . $row['id_hotel'] . "<br>";
        }
    }
}

mysqli_close($conn);
echo "<br><a href='index.php' class='btn btn-primary'>Retour à l'administration</a>";
?>
