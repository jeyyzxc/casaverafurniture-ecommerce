// Philippine Provinces and Cities Data
export interface Province {
  name: string
  code: string
  cities: string[]
}

export const philippineProvinces: Province[] = [
  {
    name: 'Metro Manila',
    code: 'NCR',
    cities: [
      'Caloocan',
      'Las Piñas',
      'Makati',
      'Malabon',
      'Mandaluyong',
      'Manila',
      'Marikina',
      'Muntinlupa',
      'Navotas',
      'Parañaque',
      'Pasay',
      'Pasig',
      'Pateros',
      'Quezon City',
      'San Juan',
      'Taguig',
      'Valenzuela'
    ]
  },
  {
    name: 'Abra',
    code: 'ABR',
    cities: ['Bangued', 'Boliney', 'Bucay', 'Bucloc', 'Daguioman', 'Danglas', 'Dolores', 'La Paz', 'Lacub', 'Lagangilang', 'Lagayan', 'Langiden', 'Licuan-Baay', 'Luba', 'Malibcong', 'Manabo', 'Peñarrubia', 'Pidigan', 'Pilar', 'Sallapadan', 'San Isidro', 'San Juan', 'San Quintin', 'Tayum', 'Tineg', 'Tubo', 'Villaviciosa']
  },
  {
    name: 'Agusan del Norte',
    code: 'AGN',
    cities: ['Buenavista', 'Butuan', 'Cabadbaran', 'Carmen', 'Jabonga', 'Kitcharao', 'Las Nieves', 'Magallanes', 'Nasipit', 'Remedios T. Romualdez', 'Santiago', 'Tubay']
  },
  {
    name: 'Agusan del Sur',
    code: 'AGS',
    cities: ['Bayugan', 'Bunawan', 'Esperanza', 'La Paz', 'Loreto', 'Prosperidad', 'Rosario', 'San Francisco', 'San Luis', 'Santa Josefa', 'Sibagat', 'Talacogon', 'Trento', 'Veruela']
  },
  {
    name: 'Aklan',
    code: 'AKL',
    cities: ['Altavas', 'Balete', 'Banga', 'Batan', 'Buruanga', 'Ibajay', 'Kalibo', 'Lezo', 'Libacao', 'Madalag', 'Makato', 'Malay', 'Malinao', 'Nabas', 'New Washington', 'Numancia', 'Tangalan']
  },
  {
    name: 'Albay',
    code: 'ALB',
    cities: ['Bacacay', 'Camalig', 'Daraga', 'Guinobatan', 'Jovellar', 'Legazpi', 'Libon', 'Ligao', 'Malilipot', 'Malinao', 'Manito', 'Oas', 'Pio Duran', 'Polangui', 'Rapu-Rapu', 'Santo Domingo', 'Tabaco', 'Tiwi']
  },
  {
    name: 'Antique',
    code: 'ANT',
    cities: ['Anini-y', 'Barbaza', 'Belison', 'Bugasong', 'Caluya', 'Culasi', 'Hamtic', 'Laua-an', 'Libertad', 'Pandan', 'Patnongon', 'San Jose', 'San Remigio', 'Sebaste', 'Sibalom', 'Tibiao', 'Tobias Fornier', 'Valderrama']
  },
  {
    name: 'Apayao',
    code: 'APA',
    cities: ['Calanasan', 'Conner', 'Flora', 'Kabugao', 'Luna', 'Pudtol', 'Santa Marcela']
  },
  {
    name: 'Aurora',
    code: 'AUR',
    cities: ['Baler', 'Casiguran', 'Dilasag', 'Dinalungan', 'Dingalan', 'Dipaculao', 'Maria Aurora', 'San Luis']
  },
  {
    name: 'Basilan',
    code: 'BAS',
    cities: ['Akbar', 'Al-Barka', 'Hadji Mohammad Ajul', 'Hadji Muhtamad', 'Isabela', 'Lamitan', 'Lantawan', 'Maluso', 'Sumisip', 'Tabuan-Lasa', 'Tipo-Tipo', 'Tuburan', 'Ungkaya Pukan']
  },
  {
    name: 'Bataan',
    code: 'BAN',
    cities: ['Abucay', 'Bagac', 'Balanga', 'Dinalupihan', 'Hermosa', 'Limay', 'Mariveles', 'Morong', 'Orani', 'Orion', 'Pilar', 'Samal']
  },
  {
    name: 'Batanes',
    code: 'BTN',
    cities: ['Basco', 'Itbayat', 'Ivana', 'Mahatao', 'Sabtang', 'Uyugan']
  },
  {
    name: 'Batangas',
    code: 'BTG',
    cities: ['Agoncillo', 'Alitagtag', 'Balayan', 'Balete', 'Bauan', 'Calaca', 'Calatagan', 'Cuenca', 'Ibaan', 'Laurel', 'Lemery', 'Lian', 'Lipa', 'Lobo', 'Mabini', 'Malvar', 'Mataasnakahoy', 'Nasugbu', 'Padre Garcia', 'Rosario', 'San Jose', 'San Juan', 'San Luis', 'San Nicolas', 'San Pascual', 'Santa Teresita', 'Santo Tomas', 'Taal', 'Talisay', 'Tanauan', 'Taysan', 'Tingloy', 'Tuy']
  },
  {
    name: 'Benguet',
    code: 'BEN',
    cities: ['Atok', 'Baguio', 'Bakun', 'Bokod', 'Buguias', 'Itogon', 'Kabayan', 'Kapangan', 'Kibungan', 'La Trinidad', 'Mankayan', 'Sablan', 'Tuba', 'Tublay']
  },
  {
    name: 'Biliran',
    code: 'BIL',
    cities: ['Almeria', 'Biliran', 'Cabucgayan', 'Caibiran', 'Culaba', 'Kawayan', 'Maripipi', 'Naval']
  },
  {
    name: 'Bohol',
    code: 'BOH',
    cities: ['Alburquerque', 'Alicia', 'Anda', 'Antequera', 'Baclayon', 'Balilihan', 'Batangas', 'Bien Unido', 'Bilar', 'Buenavista', 'Calape', 'Candijay', 'Carmen', 'Catigbian', 'Clarin', 'Corella', 'Cortes', 'Dagohoy', 'Danao', 'Dauis', 'Dimiao', 'Duero', 'Garcia Hernandez', 'Getafe', 'Guindulman', 'Inabanga', 'Jagna', 'Lila', 'Loay', 'Loboc', 'Loon', 'Mabini', 'Maribojoc', 'Panglao', 'Pilar', 'President Carlos P. Garcia', 'Sagbayan', 'San Isidro', 'San Miguel', 'Sevilla', 'Sierra Bullones', 'Sikatuna', 'Tagbilaran', 'Talibon', 'Trinidad', 'Tubigon', 'Ubay', 'Valencia']
  },
  {
    name: 'Bukidnon',
    code: 'BUK',
    cities: ['Baungon', 'Cabanglasan', 'Damulog', 'Dangcagan', 'Don Carlos', 'Impasugong', 'Kadingilan', 'Kalilangan', 'Kibawe', 'Kitaotao', 'Lantapan', 'Libona', 'Malaybalay', 'Malitbog', 'Manolo Fortich', 'Maramag', 'Pangantucan', 'Quezon', 'San Fernando', 'Sumilao', 'Talakag', 'Valencia']
  },
  {
    name: 'Bulacan',
    code: 'BUL',
    cities: ['Angat', 'Balagtas', 'Baliuag', 'Bocaue', 'Bulakan', 'Bustos', 'Calumpit', 'Doña Remedios Trinidad', 'Guiguinto', 'Hagonoy', 'Malolos', 'Marilao', 'Meycauayan', 'Norzagaray', 'Obando', 'Pandi', 'Paombong', 'Plaridel', 'Pulilan', 'San Ildefonso', 'San Jose del Monte', 'San Miguel', 'San Rafael', 'Santa Maria']
  },
  {
    name: 'Cagayan',
    code: 'CAG',
    cities: ['Abulug', 'Alcala', 'Allacapan', 'Amulung', 'Aparri', 'Baggao', 'Ballesteros', 'Buguey', 'Calayan', 'Camalaniugan', 'Claveria', 'Enrile', 'Gattaran', 'Gonzaga', 'Iguig', 'Lal-lo', 'Lasam', 'Pamplona', 'Peñablanca', 'Piat', 'Rizal', 'Sanchez-Mira', 'Santa Ana', 'Santa Praxedes', 'Santa Teresita', 'Santo Niño', 'Solana', 'Tuao', 'Tuguegarao']
  },
  {
    name: 'Camarines Norte',
    code: 'CAN',
    cities: ['Basud', 'Capalonga', 'Daet', 'Jose Panganiban', 'Labo', 'Mercedes', 'Paracale', 'San Lorenzo Ruiz', 'San Vicente', 'Santa Elena', 'Talisay', 'Vinzons']
  },
  {
    name: 'Camarines Sur',
    code: 'CAS',
    cities: ['Baao', 'Balatan', 'Bato', 'Bombon', 'Buhi', 'Bula', 'Cabusao', 'Calabanga', 'Camaligan', 'Canaman', 'Caramoan', 'Del Gallego', 'Gainza', 'Garchitorena', 'Goa', 'Iriga', 'Lagonoy', 'Libmanan', 'Lupi', 'Magarao', 'Milaor', 'Minalabac', 'Nabua', 'Ocampo', 'Pamplona', 'Pasacao', 'Pili', 'Presentacion', 'Ragay', 'Sagñay', 'San Fernando', 'San Jose', 'Sipocot', 'Siruma', 'Sorsogon', 'Tigaon', 'Tinambac']
  },
  {
    name: 'Camiguin',
    code: 'CAM',
    cities: ['Catarman', 'Guinsiliban', 'Mahinog', 'Mambajao', 'Sagay']
  },
  {
    name: 'Capiz',
    code: 'CAP',
    cities: ['Cuartero', 'Dao', 'Dumalag', 'Dumarao', 'Ivisan', 'Jamindan', 'Maayon', 'Mambusao', 'Panay', 'Panitan', 'Pilar', 'Pontevedra', 'President Roxas', 'Roxas', 'Sapian', 'Sigma', 'Tapaz']
  },
  {
    name: 'Catanduanes',
    code: 'CAT',
    cities: ['Bagamanoc', 'Baras', 'Bato', 'Caramoran', 'Gigmoto', 'Pandan', 'Panganiban', 'San Andres', 'San Miguel', 'Viga', 'Virac']
  },
  {
    name: 'Cavite',
    code: 'CAV',
    cities: ['Alfonso', 'Amadeo', 'Bacoor', 'Carmona', 'Cavite City', 'Dasmariñas', 'General Emilio Aguinaldo', 'General Mariano Alvarez', 'General Trias', 'Imus', 'Indang', 'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Naic', 'Noveleta', 'Rosario', 'Silang', 'Tagaytay', 'Tanza', 'Ternate', 'Trece Martires']
  },
  {
    name: 'Cebu',
    code: 'CEB',
    cities: ['Alcantara', 'Alcoy', 'Alegria', 'Aloguinsan', 'Argao', 'Asturias', 'Badian', 'Balamban', 'Bantayan', 'Barili', 'Bogo', 'Boljoon', 'Borbon', 'Carcar', 'Carmen', 'Catmon', 'Cebu City', 'Compostela', 'Consolacion', 'Cordova', 'Daanbantayan', 'Dalaguete', 'Danao', 'Dumanjug', 'Ginatilan', 'Lapu-Lapu', 'Liloan', 'Madridejos', 'Malabuyoc', 'Mandaue', 'Medellin', 'Minglanilla', 'Moalboal', 'Naga', 'Oslob', 'Pilar', 'Pinamungajan', 'Poro', 'Ronda', 'Samboan', 'San Fernando', 'San Francisco', 'San Remigio', 'Santa Fe', 'Santander', 'Sibonga', 'Sogod', 'Tabogon', 'Tabuelan', 'Talisay', 'Toledo', 'Tuburan', 'Tudela']
  },
  {
    name: 'Cotabato',
    code: 'NCO',
    cities: ['Alamada', 'Aleosan', 'Antipas', 'Arakan', 'Banisilan', 'Carmen', 'Kabacan', 'Kidapawan', 'Libungan', 'Magpet', 'Makilala', 'Matalam', 'Midsayap', 'M\'lang', 'Pigcawayan', 'Pikit', 'President Roxas', 'Tulunan']
  },
  {
    name: 'Davao del Norte',
    code: 'DAV',
    cities: ['Asuncion', 'Braulio E. Dujali', 'Carmen', 'Kapalong', 'New Corella', 'Panabo', 'Samal', 'San Isidro', 'Santo Tomas', 'Tagum', 'Talaingod']
  },
  {
    name: 'Davao del Sur',
    code: 'DAS',
    cities: ['Bansalan', 'Davao City', 'Digos', 'Don Marcelino', 'Hagonoy', 'Jose Abad Santos', 'Kiblawan', 'Magsaysay', 'Malalag', 'Malita', 'Matanao', 'Padada', 'Santa Cruz', 'Santa Maria', 'Sarangani', 'Sulop']
  },
  {
    name: 'Davao Occidental',
    code: 'DAO',
    cities: ['Don Marcelino', 'Jose Abad Santos', 'Malita', 'Santa Maria', 'Sarangani']
  },
  {
    name: 'Davao Oriental',
    code: 'DAO',
    cities: ['Baganga', 'Banaybanay', 'Boston', 'Caraga', 'Cateel', 'Governor Generoso', 'Lupon', 'Manay', 'Mati', 'San Isidro', 'Tarragona']
  },
  {
    name: 'Dinagat Islands',
    code: 'DIN',
    cities: ['Basilisa', 'Cagdianao', 'Dinagat', 'Libjo', 'Loreto', 'San Jose', 'Tubajon']
  },
  {
    name: 'Eastern Samar',
    code: 'EAS',
    cities: ['Arteche', 'Balangiga', 'Balangkayan', 'Borongan', 'Can-avid', 'Dolores', 'General MacArthur', 'Giporlos', 'Guiuan', 'Hernani', 'Jipapad', 'Lawaan', 'Llorente', 'Maslog', 'Maydolong', 'Mercedes', 'Oras', 'Quinapondan', 'Salcedo', 'San Julian', 'San Policarpo', 'Sulat', 'Taft']
  },
  {
    name: 'Guimaras',
    code: 'GUI',
    cities: ['Buenavista', 'Jordan', 'Nueva Valencia', 'San Lorenzo', 'Sibunag']
  },
  {
    name: 'Ifugao',
    code: 'IFU',
    cities: ['Aguinaldo', 'Alfonso Lista', 'Asipulo', 'Banaue', 'Hingyon', 'Hungduan', 'Kiangan', 'Lagawe', 'Lamut', 'Mayoyao', 'Tinoc']
  },
  {
    name: 'Ilocos Norte',
    code: 'ILN',
    cities: ['Adams', 'Bacarra', 'Badoc', 'Bangui', 'Banna', 'Batac', 'Burgos', 'Carasi', 'Currimao', 'Dingras', 'Dumalneg', 'Laoag', 'Marcos', 'Nueva Era', 'Pagudpud', 'Paoay', 'Pasuquin', 'Piddig', 'Pinili', 'San Nicolas', 'Sarrat', 'Solsona', 'Vintar']
  },
  {
    name: 'Ilocos Sur',
    code: 'ILS',
    cities: ['Alilem', 'Banayoyo', 'Bantay', 'Burgos', 'Cabugao', 'Candon', 'Caoayan', 'Cervantes', 'Galimuyod', 'Gregorio del Pilar', 'Lidlidda', 'Magsingal', 'Nagbukel', 'Narvacan', 'Quirino', 'Salcedo', 'San Emilio', 'San Esteban', 'San Ildefonso', 'San Juan', 'San Vicente', 'Santa', 'Santa Catalina', 'Santa Cruz', 'Santa Lucia', 'Santa Maria', 'Santiago', 'Santo Domingo', 'Sigay', 'Sinait', 'Sugpon', 'Suyo', 'Tagudin', 'Vigan']
  },
  {
    name: 'Iloilo',
    code: 'ILI',
    cities: ['Ajuy', 'Alimodian', 'Anilao', 'Badiangan', 'Balasan', 'Banate', 'Barotac Nuevo', 'Barotac Viejo', 'Batad', 'Bingawan', 'Cabatuan', 'Calinog', 'Carles', 'Concepcion', 'Dingle', 'Dueñas', 'Dumangas', 'Estancia', 'Guimbal', 'Igbaras', 'Janiuay', 'Lambunao', 'Leganes', 'Lemery', 'Leon', 'Maasin', 'Miagao', 'Mina', 'New Washington', 'Oton', 'Passi', 'Pavia', 'Pototan', 'San Dionisio', 'San Enrique', 'San Joaquin', 'San Miguel', 'San Rafael', 'Santa Barbara', 'Sara', 'Tigbauan', 'Tubungan', 'Zarraga']
  },
  {
    name: 'Isabela',
    code: 'ISA',
    cities: ['Alicia', 'Angadanan', 'Aurora', 'Benito Soliven', 'Burgos', 'Cabagan', 'Cabatuan', 'Cauayan', 'Cordon', 'Delfin Albano', 'Dinapigue', 'Divilacan', 'Echague', 'Gamu', 'Ilagan', 'Jones', 'Luna', 'Maconacon', 'Mallig', 'Naguilian', 'Palanan', 'Quezon', 'Quirino', 'Ramon', 'Reina Mercedes', 'Roxas', 'San Agustin', 'San Guillermo', 'San Isidro', 'San Manuel', 'San Mariano', 'San Mateo', 'San Pablo', 'Santa Maria', 'Santiago', 'Santo Tomas', 'Tumauini']
  },
  {
    name: 'Kalinga',
    code: 'KAL',
    cities: ['Balbalan', 'Lubuagan', 'Pasil', 'Pinukpuk', 'Rizal', 'Tabuk', 'Tanudan', 'Tinglayan']
  },
  {
    name: 'La Union',
    code: 'LUN',
    cities: ['Agoo', 'Aringay', 'Bacnotan', 'Bagulin', 'Balaoan', 'Bangar', 'Bauang', 'Burgos', 'Caba', 'Luna', 'Naguilian', 'Pugo', 'Rosario', 'San Fernando', 'San Gabriel', 'San Juan', 'Santo Tomas', 'Santol', 'Sudipen', 'Tubao']
  },
  {
    name: 'Laguna',
    code: 'LAG',
    cities: ['Alaminos', 'Bay', 'Biñan', 'Cabuyao', 'Calamba', 'Calauan', 'Cavinti', 'Famy', 'Kalayaan', 'Liliw', 'Los Baños', 'Luisiana', 'Lumban', 'Mabitac', 'Magdalena', 'Majayjay', 'Nagcarlan', 'Paete', 'Pagsanjan', 'Pakil', 'Pangil', 'Pila', 'Rizal', 'San Pablo', 'San Pedro', 'Santa Cruz', 'Santa Maria', 'Santa Rosa', 'Siniloan', 'Victoria']
  },
  {
    name: 'Lanao del Norte',
    code: 'LAN',
    cities: ['Bacolod', 'Baloi', 'Baroy', 'Iligan', 'Kapatagan', 'Kauswagan', 'Kolambugan', 'Lala', 'Linamon', 'Magsaysay', 'Maigo', 'Matungao', 'Munai', 'Nunungan', 'Pantao Ragat', 'Pantar', 'Poona Piagapo', 'Salvador', 'Sapad', 'Sultan Naga Dimaporo', 'Tagoloan', 'Tangcal', 'Tubod']
  },
  {
    name: 'Lanao del Sur',
    code: 'LAS',
    cities: ['Amai Manabilang', 'Bacolod-Kalawi', 'Balabagan', 'Balindong', 'Bayang', 'Binidayan', 'Buadiposo-Buntong', 'Bubong', 'Butig', 'Calanogas', 'Ditsaan-Ramain', 'Ganassi', 'Kapai', 'Kapatagan', 'Lumbaca-Unayan', 'Lumbatan', 'Lumbayanague', 'Madalum', 'Madamba', 'Maguing', 'Malabang', 'Marantao', 'Marawi', 'Marogong', 'Masiu', 'Mulondo', 'Pagayawan', 'Piagapo', 'Poona Bayabao', 'Pualas', 'Saguiaran', 'Sultan Dumalondong', 'Tagoloan II', 'Tamparan', 'Taraka', 'Tubaran', 'Tugaya', 'Wao']
  },
  {
    name: 'Leyte',
    code: 'LEY',
    cities: ['Abuyog', 'Alangalang', 'Albuera', 'Babatngon', 'Barugo', 'Bato', 'Baybay', 'Burauen', 'Calubian', 'Capoocan', 'Carigara', 'Dagami', 'Dulag', 'Hilongos', 'Hindang', 'Inopacan', 'Isabel', 'Jaro', 'Javier', 'Julita', 'Kananga', 'La Paz', 'Leyte', 'MacArthur', 'Mahaplag', 'Matag-ob', 'Matalom', 'Mayorga', 'Merida', 'Ormoc', 'Palo', 'Palompon', 'Pastrana', 'San Isidro', 'San Miguel', 'Santa Fe', 'Tabango', 'Tabontabon', 'Tacloban', 'Tanauan', 'Tolosa', 'Tunga', 'Villaba']
  },
  {
    name: 'Maguindanao',
    code: 'MAG',
    cities: ['Ampatuan', 'Barira', 'Buldon', 'Buluan', 'Datu Abdullah Sangki', 'Datu Anggal Midtimbang', 'Datu Blah T. Sinsuat', 'Datu Hoffer Ampatuan', 'Datu Montawal', 'Datu Odin Sinsuat', 'Datu Paglas', 'Datu Piang', 'Datu Salibo', 'Datu Saudi-Ampatuan', 'Datu Unsay', 'General Salipada K. Pendatun', 'Guindulungan', 'Kabuntalan', 'Mamasapano', 'Mangudadatu', 'Matanog', 'Northern Kabuntalan', 'Pagalungan', 'Paglat', 'Pandag', 'Parang', 'Rajah Buayan', 'Shariff Aguak', 'Shariff Saydona Mustapha', 'South Upi', 'Sultan Kudarat', 'Sultan Mastura', 'Sultan sa Barongis', 'Talayan', 'Talitay', 'Upi']
  },
  {
    name: 'Marinduque',
    code: 'MAD',
    cities: ['Boac', 'Buenavista', 'Gasan', 'Mogpog', 'Santa Cruz', 'Torrijos']
  },
  {
    name: 'Masbate',
    code: 'MAS',
    cities: ['Aroroy', 'Baleno', 'Balud', 'Batuan', 'Cataingan', 'Cawayan', 'Claveria', 'Dimasalang', 'Esperanza', 'Mandaon', 'Masbate City', 'Milagros', 'Mobo', 'Monreal', 'Palanas', 'Pio V. Corpuz', 'Placer', 'San Fernando', 'San Jacinto', 'San Pascual', 'Uson']
  },
  {
    name: 'Misamis Occidental',
    code: 'MSC',
    cities: ['Aloran', 'Baliangao', 'Bonifacio', 'Calamba', 'Clarin', 'Concepcion', 'Don Victoriano Chiongbian', 'Jimenez', 'Lopez Jaena', 'Oroquieta', 'Ozamiz', 'Panaon', 'Plaridel', 'Sapang Dalaga', 'Sinacaban', 'Tangub', 'Tudela']
  },
  {
    name: 'Misamis Oriental',
    code: 'MSR',
    cities: ['Alubijid', 'Balingasag', 'Balingoan', 'Binuangan', 'Cagayan de Oro', 'Claveria', 'El Salvador', 'Gingoog', 'Gitagum', 'Initao', 'Jasaan', 'Kinoguitan', 'Lagonglong', 'Laguindingan', 'Libertad', 'Lugait', 'Magsaysay', 'Manticao', 'Medina', 'Naawan', 'Opol', 'Salay', 'Sugbongcogon', 'Tagoloan', 'Talisayan', 'Villanueva']
  },
  {
    name: 'Mountain Province',
    code: 'MOU',
    cities: ['Barlig', 'Bauko', 'Besao', 'Bontoc', 'Natonin', 'Paracelis', 'Sabangan', 'Sadanga', 'Sagada', 'Tadian']
  },
  {
    name: 'Negros Occidental',
    code: 'NEC',
    cities: ['Bacolod', 'Bago', 'Binalbagan', 'Cadiz', 'Calatrava', 'Candoni', 'Cauayan', 'Enrique B. Magalona', 'Escalante', 'Himamaylan', 'Hinigaran', 'Hinoba-an', 'Ilog', 'Isabela', 'Kabankalan', 'La Carlota', 'La Castellana', 'Manapla', 'Moises Padilla', 'Murcia', 'Pontevedra', 'Pulupandan', 'Sagay', 'Salvador Benedicto', 'San Carlos', 'San Enrique', 'Silay', 'Sipalay', 'Talisay', 'Toboso', 'Valladolid', 'Victorias']
  },
  {
    name: 'Negros Oriental',
    code: 'NER',
    cities: ['Amlan', 'Ayungon', 'Bacong', 'Bais', 'Basay', 'Bayawan', 'Bindoy', 'Canlaon', 'Dauin', 'Dumaguete', 'Guihulngan', 'Jimalalud', 'La Libertad', 'Mabinay', 'Manjuyod', 'Pamplona', 'San Jose', 'Santa Catalina', 'Siaton', 'Sibulan', 'Tanjay', 'Tayasan', 'Valencia', 'Vallehermoso', 'Zamboanguita']
  },
  {
    name: 'Northern Samar',
    code: 'NSA',
    cities: ['Allen', 'Biri', 'Bobon', 'Capul', 'Catarman', 'Catubig', 'Gamay', 'Laoang', 'Lapinig', 'Las Navas', 'Lavezares', 'Lope de Vega', 'Mapanas', 'Mondragon', 'Palapag', 'Pambujan', 'Rosario', 'San Antonio', 'San Isidro', 'San Jose', 'San Roque', 'San Vicente', 'Silvino Lobos', 'Victoria']
  },
  {
    name: 'Nueva Ecija',
    code: 'NUE',
    cities: ['Aliaga', 'Bongabon', 'Cabanatuan', 'Cabiao', 'Carranglan', 'Cuyapo', 'Gabaldon', 'Gapan', 'General Mamerto Natividad', 'General Tinio', 'Guimba', 'Jaen', 'Laur', 'Licab', 'Llanera', 'Lupao', 'Nampicuan', 'Palayan', 'Pantabangan', 'Peñaranda', 'Quezon', 'Rizal', 'San Antonio', 'San Isidro', 'San Jose', 'San Leonardo', 'Santa Rosa', 'Santo Domingo', 'Science City of Muñoz', 'Talavera', 'Talugtug', 'Zaragoza']
  },
  {
    name: 'Nueva Vizcaya',
    code: 'NUV',
    cities: ['Alfonso Castaneda', 'Ambaguio', 'Aritao', 'Bagabag', 'Bambang', 'Bayombong', 'Diadi', 'Dupax del Norte', 'Dupax del Sur', 'Kasibu', 'Kayapa', 'Quezon', 'Santa Fe', 'Solano', 'Villaverde']
  },
  {
    name: 'Occidental Mindoro',
    code: 'MDC',
    cities: ['Abra de Ilog', 'Calintaan', 'Lubang', 'Magsaysay', 'Mamburao', 'Paluan', 'Rizal', 'Sablayan', 'San Jose', 'Santa Cruz']
  },
  {
    name: 'Oriental Mindoro',
    code: 'MDR',
    cities: ['Baco', 'Bansud', 'Bongabong', 'Bulalacao', 'Calapan', 'Gloria', 'Mansalay', 'Naujan', 'Pinamalayan', 'Pola', 'Puerto Galera', 'Roxas', 'San Teodoro', 'Socorro', 'Victoria']
  },
  {
    name: 'Palawan',
    code: 'PLW',
    cities: ['Aborlan', 'Agutaya', 'Araceli', 'Balabac', 'Bataraza', 'Brooke\'s Point', 'Busuanga', 'Cagayancillo', 'Coron', 'Culion', 'Cuyo', 'Dumaran', 'El Nido', 'Kalayaan', 'Linapacan', 'Magsaysay', 'Narra', 'Puerto Princesa', 'Quezon', 'Rizal', 'Roxas', 'San Vicente', 'Sofronio Española', 'Taytay']
  },
  {
    name: 'Pampanga',
    code: 'PAM',
    cities: ['Angeles', 'Apalit', 'Arayat', 'Bacolor', 'Candaba', 'Floridablanca', 'Guagua', 'Lubao', 'Mabalacat', 'Macabebe', 'Magalang', 'Masantol', 'Mexico', 'Minalin', 'Porac', 'San Fernando', 'San Luis', 'San Simon', 'Santa Ana', 'Santa Rita', 'Santo Tomas', 'Sasmuan']
  },
  {
    name: 'Pangasinan',
    code: 'PAN',
    cities: ['Agno', 'Aguilar', 'Alaminos', 'Alcala', 'Anda', 'Asingan', 'Balungao', 'Bani', 'Basista', 'Bautista', 'Bayambang', 'Binalonan', 'Binmaley', 'Bolinao', 'Bugallon', 'Burgos', 'Calasiao', 'Dagupan', 'Dasol', 'Infanta', 'Labrador', 'Laoac', 'Lingayen', 'Mabini', 'Malasiqui', 'Manaoag', 'Mangaldan', 'Mangatarem', 'Mapandan', 'Natividad', 'Pozorrubio', 'Rosales', 'San Carlos', 'San Fabian', 'San Jacinto', 'San Manuel', 'San Nicolas', 'San Quintin', 'Santa Barbara', 'Santa Maria', 'Santo Tomas', 'Sison', 'Sual', 'Tayug', 'Umingan', 'Urbiztondo', 'Villasis']
  },
  {
    name: 'Quezon',
    code: 'QUE',
    cities: ['Agdangan', 'Alabat', 'Atimonan', 'Buenavista', 'Burdeos', 'Calauag', 'Candelaria', 'Catanauan', 'Dolores', 'General Luna', 'General Nakar', 'Guinayangan', 'Gumaca', 'Infanta', 'Jomalig', 'Lopez', 'Lucban', 'Lucena', 'Macalelon', 'Mauban', 'Mulanay', 'Padre Burgos', 'Pagbilao', 'Panukulan', 'Patnanungan', 'Perez', 'Pitogo', 'Plaridel', 'Polillo', 'Quezon', 'Real', 'Sampaloc', 'San Andres', 'San Antonio', 'San Francisco', 'San Narciso', 'Sariaya', 'Tagkawayan', 'Tayabas', 'Tiaong', 'Unisan']
  },
  {
    name: 'Quirino',
    code: 'QUI',
    cities: ['Aglipay', 'Cabarroguis', 'Diffun', 'Maddela', 'Nagtipunan', 'Saguday']
  },
  {
    name: 'Rizal',
    code: 'RIZ',
    cities: ['Angono', 'Antipolo', 'Baras', 'Binangonan', 'Cainta', 'Cardona', 'Jalajala', 'Morong', 'Pililla', 'Rodriguez', 'San Mateo', 'Tanay', 'Taytay', 'Teresa']
  },
  {
    name: 'Romblon',
    code: 'ROM',
    cities: ['Alcantara', 'Banton', 'Cajidiocan', 'Calatrava', 'Concepcion', 'Corcuera', 'Ferrol', 'Looc', 'Magdiwang', 'Odiongan', 'Romblon', 'San Agustin', 'San Andres', 'San Fernando', 'San Jose', 'Santa Fe', 'Santa Maria']
  },
  {
    name: 'Samar',
    code: 'WSA',
    cities: ['Almagro', 'Basey', 'Calbayog', 'Calbiga', 'Catbalogan', 'Daram', 'Gandara', 'Hinabangan', 'Jiabong', 'Marabut', 'Matuguinao', 'Motiong', 'Pagsanghan', 'Paranas', 'Pinabacdao', 'San Jorge', 'San Jose de Buan', 'San Sebastian', 'Santa Margarita', 'Santa Rita', 'Santo Niño', 'Tagapul-an', 'Talalora', 'Tarangnan', 'Villareal', 'Zumarraga']
  },
  {
    name: 'Sarangani',
    code: 'SAR',
    cities: ['Alabel', 'Glan', 'Kiamba', 'Maasim', 'Maitum', 'Malapatan', 'Malungon']
  },
  {
    name: 'Siquijor',
    code: 'SIG',
    cities: ['Enrile', 'Larena', 'Lazi', 'Maria', 'San Juan', 'Siquijor']
  },
  {
    name: 'Sorsogon',
    code: 'SOR',
    cities: ['Barcelona', 'Bulan', 'Bulusan', 'Casiguran', 'Castilla', 'Donsol', 'Gubat', 'Irosin', 'Juban', 'Magallanes', 'Matnog', 'Pilar', 'Prieto Diaz', 'Santa Magdalena', 'Sorsogon City']
  },
  {
    name: 'South Cotabato',
    code: 'SCO',
    cities: ['Banga', 'General Santos', 'Koronadal', 'Lake Sebu', 'Norala', 'Polomolok', 'Santo Niño', 'Surallah', 'Tampakan', 'Tantangan', 'T\'boli', 'Tupi']
  },
  {
    name: 'Southern Leyte',
    code: 'SLE',
    cities: ['Anahawan', 'Bontoc', 'Hinunangan', 'Hinundayan', 'Libagon', 'Liloan', 'Limasawa', 'Maasin', 'Macrohon', 'Malitbog', 'Padre Burgos', 'Pintuyan', 'Saint Bernard', 'San Francisco', 'San Juan', 'San Ricardo', 'Silago', 'Sogod', 'Tomas Oppus']
  },
  {
    name: 'Sultan Kudarat',
    code: 'SUK',
    cities: ['Bagumbayan', 'Columbio', 'Esperanza', 'Isulan', 'Kalamansig', 'Lambayong', 'Lebak', 'Lutayan', 'Palimbang', 'President Quirino', 'Senator Ninoy Aquino', 'Tacurong']
  },
  {
    name: 'Sulu',
    code: 'SLU',
    cities: ['Banguingui', 'Hadji Panglima Tahil', 'Indanan', 'Jolo', 'Kalingalan Caluang', 'Lugus', 'Luuk', 'Maimbung', 'Old Panamao', 'Omar', 'Pandami', 'Panglima Estino', 'Pangutaran', 'Parang', 'Pata', 'Patikul', 'Siasi', 'Talipao', 'Tapul', 'Tongkil']
  },
  {
    name: 'Surigao del Norte',
    code: 'SUN',
    cities: ['Alegria', 'Bacuag', 'Burgos', 'Claver', 'Dapa', 'Del Carmen', 'General Luna', 'Gigaquit', 'Mainit', 'Malimono', 'Pilar', 'Placer', 'San Benito', 'San Francisco', 'San Isidro', 'Santa Monica', 'Sison', 'Socorro', 'Surigao City', 'Tagana-an', 'Tubod']
  },
  {
    name: 'Surigao del Sur',
    code: 'SUR',
    cities: ['Barobo', 'Bayabas', 'Bislig', 'Cagwait', 'Cantilan', 'Carmen', 'Carrascal', 'Cortes', 'Hinatuan', 'Lanuza', 'Lianga', 'Lingig', 'Madrid', 'Marihatag', 'San Agustin', 'San Miguel', 'Tagbina', 'Tago', 'Tandag']
  },
  {
    name: 'Tarlac',
    code: 'TAR',
    cities: ['Anao', 'Bamban', 'Camiling', 'Capas', 'Concepcion', 'Gerona', 'La Paz', 'Mayantoc', 'Moncada', 'Paniqui', 'Pura', 'Ramos', 'San Clemente', 'San Jose', 'San Manuel', 'Santa Ignacia', 'Tarlac City', 'Victoria']
  },
  {
    name: 'Tawi-Tawi',
    code: 'TAW',
    cities: ['Bongao', 'Languyan', 'Mapun', 'Panglima Sugala', 'Sapa-Sapa', 'Sibutu', 'Simunul', 'Sitangkai', 'South Ubian', 'Tandubas', 'Turtle Islands']
  },
  {
    name: 'Zambales',
    code: 'ZMB',
    cities: ['Botolan', 'Cabangan', 'Candelaria', 'Castillejos', 'Iba', 'Masinloc', 'Olongapo', 'Palauig', 'San Antonio', 'San Felipe', 'San Marcelino', 'San Narciso', 'Santa Cruz', 'Subic']
  },
  {
    name: 'Zamboanga del Norte',
    code: 'ZAN',
    cities: ['Bacungan', 'Baliguian', 'Godod', 'Gutalac', 'Jose Dalman', 'Kalawit', 'Katipunan', 'La Libertad', 'Labason', 'Liloy', 'Manukan', 'Mutia', 'Piñan', 'Polanco', 'President Manuel A. Roxas', 'Rizal', 'Salug', 'Sergio Osmeña Sr.', 'Siayan', 'Sibuco', 'Sibutad', 'Sindangan', 'Siocon', 'Sirawai', 'Tampilisan', 'Dapitan', 'Dipolog']
  },
  {
    name: 'Zamboanga del Sur',
    code: 'ZAS',
    cities: ['Aurora', 'Bayog', 'Dimataling', 'Dinas', 'Dumalinao', 'Dumingag', 'Guipos', 'Josefina', 'Kumalarang', 'Labangan', 'Lakewood', 'Lapuyan', 'Mahayag', 'Margosatubig', 'Midsalip', 'Molave', 'Pagadian', 'Pitogo', 'Ramon Magsaysay', 'San Miguel', 'San Pablo', 'Sominot', 'Tabina', 'Tambulig', 'Tigbao', 'Tukuran', 'Vincenzo A. Sagun', 'Zamboanga City']
  },
  {
    name: 'Zamboanga Sibugay',
    code: 'ZSI',
    cities: ['Alicia', 'Buug', 'Diplahan', 'Imelda', 'Ipil', 'Kabasalan', 'Mabuhay', 'Malangas', 'Naga', 'Olutanga', 'Payao', 'Roseller Lim', 'Siay', 'Talusan', 'Titay', 'Tungawan']
  }
]

// Helper function to get cities by province
export const getCitiesByProvince = (provinceName: string): string[] => {
  const province = philippineProvinces.find(p => p.name === provinceName)
  return province ? province.cities : []
}

// Helper function to get all province names
export const getProvinceNames = (): string[] => {
  return philippineProvinces.map(p => p.name)
}

// Postal code mapping by province and city
// Format: 'Province|City' => 'PostalCode'
export const postalCodeMap: Record<string, string> = {
  // Metro Manila
  'Metro Manila|Manila': '1000',
  'Metro Manila|Quezon City': '1100',
  'Metro Manila|Caloocan': '1400',
  'Metro Manila|Las Piñas': '1740',
  'Metro Manila|Makati': '1200',
  'Metro Manila|Malabon': '1470',
  'Metro Manila|Mandaluyong': '1550',
  'Metro Manila|Marikina': '1800',
  'Metro Manila|Muntinlupa': '1776',
  'Metro Manila|Navotas': '1485',
  'Metro Manila|Parañaque': '1700',
  'Metro Manila|Pasay': '1300',
  'Metro Manila|Pasig': '1600',
  'Metro Manila|Pateros': '1620',
  'Metro Manila|San Juan': '1500',
  'Metro Manila|Taguig': '1630',
  'Metro Manila|Valenzuela': '1440',
  
  // Cavite (Calabarzon)
  'Cavite|Bacoor': '4102',
  'Cavite|Imus': '4103',
  'Cavite|Dasmariñas': '4114',
  'Cavite|Cavite City': '4100',
  'Cavite|Tagaytay': '4120',
  'Cavite|General Trias': '4107',
  'Cavite|Rosario': '4106',
  'Cavite|Tanza': '4108',
  'Cavite|Trece Martires': '4109',
  'Cavite|Carmona': '4116',
  'Cavite|Silang': '4118',
  'Cavite|Alfonso': '4123',
  'Cavite|Amadeo': '4119',
  'Cavite|General Emilio Aguinaldo': '4124',
  'Cavite|General Mariano Alvarez': '4117',
  'Cavite|Indang': '4122',
  'Cavite|Kawit': '4104',
  'Cavite|Magallanes': '4113',
  'Cavite|Maragondon': '4112',
  'Cavite|Mendez': '4121',
  'Cavite|Naic': '4110',
  'Cavite|Noveleta': '4105',
  
  // Laguna (Calabarzon)
  'Laguna|Calamba': '4027',
  'Laguna|San Pedro': '4023',
  'Laguna|Santa Rosa': '4026',
  'Laguna|Biñan': '4024',
  'Laguna|Cabuyao': '4025',
  'Laguna|San Pablo': '4000',
  'Laguna|Los Baños': '4030',
  'Laguna|Alaminos': '4001',
  'Laguna|Bay': '4033',
  'Laguna|Calauan': '4012',
  'Laguna|Cavinti': '4013',
  'Laguna|Famy': '4021',
  'Laguna|Kalayaan': '4015',
  'Laguna|Liliw': '4004',
  'Laguna|Luisiana': '4032',
  'Laguna|Lumban': '4014',
  'Laguna|Mabitac': '4020',
  'Laguna|Magdalena': '4007',
  'Laguna|Majayjay': '4005',
  'Laguna|Nagcarlan': '4002',
  'Laguna|Paete': '4016',
  'Laguna|Pagsanjan': '4008',
  'Laguna|Pakil': '4018',
  'Laguna|Pangil': '4017',
  'Laguna|Pila': '4010',
  'Laguna|Rizal': '4003',
  'Laguna|Santa Cruz': '4009',
  'Laguna|Santa Maria': '4022',
  'Laguna|Siniloan': '4019',
  'Laguna|Victoria': '4011',
  
  // Batangas (Calabarzon)
  'Batangas|Batangas City': '4200',
  'Batangas|Lipa': '4217',
  'Batangas|Tanauan': '4232',
  'Batangas|Nasugbu': '4231',
  'Batangas|Calaca': '4212',
  'Batangas|Lemery': '4209',
  'Batangas|Bauan': '4201',
  'Batangas|Balayan': '4213',
  'Batangas|Agoncillo': '4211',
  'Batangas|Alitagtag': '4205',
  'Batangas|Balete': '4219',
  'Batangas|Calatagan': '4215',
  'Batangas|Cuenca': '4222',
  'Batangas|Ibaan': '4230',
  'Batangas|Laurel': '4221',
  'Batangas|Lian': '4216',
  'Batangas|Lobo': '4229',
  'Batangas|Mabini': '4202',
  'Batangas|Malvar': '4233',
  'Batangas|Mataasnakahoy': '4223',
  'Batangas|Padre Garcia': '4224',
  'Batangas|Rosario': '4225',
  'Batangas|San Jose': '4227',
  'Batangas|San Juan': '4226',
  'Batangas|San Luis': '4210',
  'Batangas|San Nicolas': '4207',
  'Batangas|San Pascual': '4204',
  'Batangas|Santa Teresita': '4206',
  'Batangas|Santo Tomas': '4234',
  'Batangas|Taal': '4208',
  'Batangas|Talisay': '4220',
  'Batangas|Taysan': '4228',
  'Batangas|Tingloy': '4203',
  'Batangas|Tuy': '4214',
  
  // Rizal (Calabarzon)
  'Rizal|Antipolo': '1870',
  'Rizal|Taytay': '1920',
  'Rizal|Cainta': '1900',
  'Rizal|San Mateo': '1850',
  'Rizal|Angono': '1930',
  'Rizal|Baras': '1970',
  'Rizal|Binangonan': '1940',
  'Rizal|Cardona': '1950',
  'Rizal|Jalajala': '1990',
  'Rizal|Morong': '1960',
  'Rizal|Pililla': '1910',
  'Rizal|Rodriguez': '1860',
  'Rizal|Tanay': '1980',
  'Rizal|Teresa': '1880',
  
  // Bulacan (Central Luzon)
  'Bulacan|Malolos': '3000',
  'Bulacan|San Jose del Monte': '3023',
  'Bulacan|Meycauayan': '3020',
  'Bulacan|Marilao': '3019',
  'Bulacan|Baliuag': '3006',
  'Bulacan|Plaridel': '3004',
  'Bulacan|Pulilan': '3005',
  'Bulacan|Calumpit': '3003',
  'Bulacan|Hagonoy': '3002',
  'Bulacan|Paombong': '3001',
  'Bulacan|Guiguinto': '3015',
  'Bulacan|Balagtas': '3016',
  'Bulacan|Bocaue': '3018',
  'Bulacan|Bulakan': '3017',
  'Bulacan|Bustos': '3007',
  'Bulacan|Doña Remedios Trinidad': '3009',
  'Bulacan|Norzagaray': '3013',
  'Bulacan|Obando': '3021',
  'Bulacan|Pandi': '3014',
  'Bulacan|San Ildefonso': '3010',
  'Bulacan|San Miguel': '3011',
  'Bulacan|San Rafael': '3008',
  'Bulacan|Santa Maria': '3022',
  'Bulacan|Angat': '3012',
  
  // Pampanga (Central Luzon)
  'Pampanga|San Fernando': '2000',
  'Pampanga|Angeles': '2009',
  'Pampanga|Mabalacat': '2010',
  'Pampanga|Apalit': '2016',
  'Pampanga|Arayat': '2012',
  'Pampanga|Bacolor': '2001',
  'Pampanga|Candaba': '2013',
  'Pampanga|Floridablanca': '2006',
  'Pampanga|Guagua': '2003',
  'Pampanga|Lubao': '2005',
  'Pampanga|Macabebe': '2018',
  'Pampanga|Magalang': '2011',
  'Pampanga|Masantol': '2017',
  'Pampanga|Mexico': '2021',
  'Pampanga|Minalin': '2019',
  'Pampanga|Porac': '2008',
  'Pampanga|San Luis': '2014',
  'Pampanga|San Simon': '2015',
  'Pampanga|Santa Ana': '2022',
  'Pampanga|Santa Rita': '2002',
  'Pampanga|Santo Tomas': '2020',
  'Pampanga|Sasmuan': '2004',
  
  // Cebu (Central Visayas)
  'Cebu|Cebu City': '6000',
  'Cebu|Lapu-Lapu': '6015',
  'Cebu|Mandaue': '6014',
  'Cebu|Talisay': '6045',
  'Cebu|Toledo': '6038',
  'Cebu|Danao': '6004',
  'Cebu|Bogo': '6010',
  'Cebu|Carcar': '6019',
  'Cebu|Naga': '6037',
  'Cebu|Consolacion': '6001',
  'Cebu|Liloan': '6002',
  'Cebu|Compostela': '6003',
  'Cebu|Minglanilla': '6046',
  'Cebu|San Fernando': '6018',
  'Cebu|Cordova': '6017',
  'Cebu|Alcantara': '6033',
  'Cebu|Alcoy': '6023',
  'Cebu|Alegria': '6030',
  'Cebu|Aloguinsan': '6040',
  'Cebu|Argao': '6021',
  'Cebu|Asturias': '6042',
  'Cebu|Badian': '6031',
  'Cebu|Balamban': '6041',
  'Cebu|Bantayan': '6052',
  'Cebu|Barili': '6036',
  'Cebu|Boljoon': '6024',
  'Cebu|Borbon': '6008',
  'Cebu|Carmen': '6005',
  'Cebu|Catmon': '6006',
  'Cebu|Daanbantayan': '6013',
  'Cebu|Dalaguete': '6022',
  'Cebu|Dumanjug': '6035',
  'Cebu|Ginatilan': '6026',
  'Cebu|Madridejos': '6053',
  'Cebu|Malabuyoc': '6027',
  'Cebu|Medellin': '6012',
  'Cebu|Moalboal': '6032',
  'Cebu|Oslob': '6025',
  'Cebu|Pilar': '6048',
  'Cebu|Pinamungajan': '6039',
  'Cebu|Poro': '6049',
  'Cebu|Ronda': '6034',
  'Cebu|Samboan': '6028',
  'Cebu|San Francisco': '6050',
  'Cebu|San Remigio': '6011',
  'Cebu|Santa Fe': '6047',
  'Cebu|Santander': '6029',
  'Cebu|Sibonga': '6020',
  'Cebu|Sogod': '6007',
  'Cebu|Tabogon': '6009',
  'Cebu|Tabuelan': '6044',
  'Cebu|Tuburan': '6043',
  'Cebu|Tudela': '6051',
  
  // Davao del Sur (Davao Region)
  'Davao del Sur|Davao City': '8000',
  'Davao del Sur|Digos': '8002',
  'Davao del Sur|Bansalan': '8005',
  'Davao del Sur|Don Marcelino': '8013',
  'Davao del Sur|Hagonoy': '8006',
  'Davao del Sur|Jose Abad Santos': '8014',
  'Davao del Sur|Kiblawan': '8008',
  'Davao del Sur|Magsaysay': '8004',
  'Davao del Sur|Malalag': '8010',
  'Davao del Sur|Malita': '8012',
  'Davao del Sur|Matanao': '8003',
  'Davao del Sur|Padada': '8007',
  'Davao del Sur|Santa Cruz': '8001',
  'Davao del Sur|Santa Maria': '8011',
  'Davao del Sur|Sarangani': '8015',
  'Davao del Sur|Sulop': '8009',
  
  // Default postal codes for common provinces (fallback)
  'Quezon|Lucena': '4301',
  'Quezon|Tayabas': '4327',
  'Quezon|Sariaya': '4322',
  'Quezon|Candelaria': '4323',
  'Quezon|Tiaong': '4325',
  'Quezon|San Pablo': '4000',
}

// Helper function to get postal code by province and city
export const getPostalCode = (province: string, city: string): string | null => {
  if (!province || !city) return null
  const key = `${province}|${city}`
  return postalCodeMap[key] || null
}
