-- ============================================================
--  demo seed data (Aug-Oct 2026) - generated from the Excel demo
--  Import AFTER schema.sql into the DB named in .envSalesTourism.
-- ============================================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE users; TRUNCATE list_items; TRUNCATE fx_rates; TRUNCATE settings;
TRUNCATE clients; TRUNCATE inquiries; TRUNCATE opportunities; TRUNCATE quotations;
TRUNCATE follow_ups; TRUNCATE bookings; TRUNCATE payment_schedule; TRUNCATE payments;
TRUNCATE sales_targets; TRUNCATE activities;

-- users - password for everyone: demo1234 (change after first login!)
INSERT INTO users (id,name,email,salt,password_hash,role,active) VALUES (1,'System Admin','admin@michaeltourism.com','f226d5e63ff6093e6910e4db036ef89c','b9802cc55db08c2840a6add5d198707d4f82662205e97da4347bdb9b634d475f','admin',1);
INSERT INTO users (id,name,email,salt,password_hash,role,active) VALUES (2,'Kelvin Mtui','kelvin.mtui@michaeltourism.com','f96b78a9ce3e407a509ca3965afdf386','2c548daff9cb7c152b0bae39e252bc756b183fce7d51460357640d89bf860f3e','manager',1);
INSERT INTO users (id,name,email,salt,password_hash,role,active) VALUES (3,'Amina Hassan','amina.hassan@michaeltourism.com','c98833fe0dc25782f5038f8603349839','2b7a8dfdb6e3fdfb06100e53bd94f7fde5e48a60bc62d695b65d14d7b74a6871','sales',1);
INSERT INTO users (id,name,email,salt,password_hash,role,active) VALUES (4,'Joseph Mkumbo','joseph.mkumbo@michaeltourism.com','ade592c0012572e9a6dcf7d4028d00e6','75b1e14276020c9bd2b2bc2f1a09b601d6a74d4e069b6ce35fa578063ee38441','sales',1);
INSERT INTO users (id,name,email,salt,password_hash,role,active) VALUES (5,'Grace Mushi','grace.mushi@michaeltourism.com','bcd7abbb7550d60a9c42d05dd587a2ba','7e8e5b52ac0a90ec0ea5762bc362608baa4993f4d5bc209bb15b7343e5683e37','sales',1);

-- dropdown lists
INSERT INTO list_items (list_name,value,sort) VALUES ('lead_source','Website',1), ('lead_source','Email',2), ('lead_source','Phone',3), ('lead_source','WhatsApp',4), ('lead_source','Social Media',5), ('lead_source','Travel Agency',6), ('lead_source','Referral',7), ('lead_source','Trade Fair',8), ('lead_source','Repeat Client',9), ('lead_source','Other',10);
INSERT INTO list_items (list_name,value,sort) VALUES ('sales_stage','New Inquiry',1), ('sales_stage','Qualified',2), ('sales_stage','Quotation Sent',3), ('sales_stage','Follow-Up',4), ('sales_stage','Negotiation',5), ('sales_stage','Confirmed',6), ('sales_stage','Lost',7), ('sales_stage','Inactive',8);
INSERT INTO list_items (list_name,value,sort) VALUES ('lead_status','Open',1), ('lead_status','Won',2), ('lead_status','Lost',3), ('lead_status','Inactive',4);
INSERT INTO list_items (list_name,value,sort) VALUES ('quotation_status','Draft',1), ('quotation_status','Sent',2), ('quotation_status','Revised',3), ('quotation_status','Accepted',4), ('quotation_status','Rejected',5), ('quotation_status','Expired',6);
INSERT INTO list_items (list_name,value,sort) VALUES ('booking_status','Tentative',1), ('booking_status','Confirmed',2), ('booking_status','In Progress',3), ('booking_status','Completed',4), ('booking_status','Cancelled',5);
INSERT INTO list_items (list_name,value,sort) VALUES ('task_status','Pending',1), ('task_status','In Progress',2), ('task_status','Completed',3), ('task_status','Cancelled',4);
INSERT INTO list_items (list_name,value,sort) VALUES ('priority','High',1), ('priority','Medium',2), ('priority','Low',3);
INSERT INTO list_items (list_name,value,sort) VALUES ('activity_type','Call',1), ('activity_type','Email',2), ('activity_type','WhatsApp',3), ('activity_type','Meeting',4), ('activity_type','Quotation Sent',5), ('activity_type','Follow-Up',6), ('activity_type','Other',7);
INSERT INTO list_items (list_name,value,sort) VALUES ('client_type','Individual',1), ('client_type','Travel Agency',2), ('client_type','Corporate',3), ('client_type','Tour Operator',4), ('client_type','NGO / Institution',5), ('client_type','Other',6);
INSERT INTO list_items (list_name,value,sort) VALUES ('payment_type','Deposit',1), ('payment_type','Installment',2), ('payment_type','Final Payment',3), ('payment_type','Refund',4);
INSERT INTO list_items (list_name,value,sort) VALUES ('payment_method','Bank Transfer',1), ('payment_method','Cash',2), ('payment_method','Mobile Money',3), ('payment_method','Card',4), ('payment_method','Online Gateway',5), ('payment_method','Other',6);
INSERT INTO list_items (list_name,value,sort) VALUES ('payment_status','Unpaid',1), ('payment_status','Partially Paid',2), ('payment_status','Paid',3), ('payment_status','Overdue',4), ('payment_status','Refunded',5);
INSERT INTO list_items (list_name,value,sort) VALUES ('yes_no','Yes',1), ('yes_no','No',2);
INSERT INTO list_items (list_name,value,sort) VALUES ('currency','TZS',1), ('currency','USD',2), ('currency','EUR',3), ('currency','GBP',4), ('currency','KES',5);

-- fx rates to base currency (editable in Settings)
INSERT INTO fx_rates (currency,rate_to_base) VALUES ('USD',1.0), ('TZS',0.000385), ('EUR',1.09), ('GBP',1.27), ('KES',0.0077);
INSERT INTO settings (k,v) VALUES ('company_name','Michael Tourism'),('base_currency','USD');

-- clients
INSERT INTO clients (id,name,client_type,country,contact_person,email,phone,first_inquiry,owner_id,notes) VALUES
(1,'Wild Roots Travel Ltd','Travel Agency','Nairobi, Kenya','Mercy Kariuki','mercy@wildrootstravel.co.ke','+254 722 481 090','2026-08-01',2,'Repeat agency; multiple inquiries YTD.'),
(2,'EuropaReisen GmbH','Travel Agency','Munich, Germany','Klaus Weber','k.weber@europareisen.de','+49 89 220 4512','2026-08-02',3,'Charter group planned for January 2027.'),
(3,'Chiara Rossi','Individual','Milan, Italy','Chiara Rossi','chiara.rossi@libero.it','+39 348 921 6640','2026-08-03',4,''),
(4,'Hans Müller','Individual','Stuttgart, Germany','Hans Müller','hans.mueller@web.de','+49 170 338 2211','2026-08-03',5,''),
(5,'Arusha Coffee Collective','Corporate','Arusha, Tanzania','Avash Nyundo','a.nyundo@kibocoffee.co.tz','+255 767 220 540','2026-08-05',4,''),
(6,'Serengeti Gateway Ltd','Travel Agency','Arusha, Tanzania','Baraka Msuya','bookings@serengetigateway.co.tz','+255 784 902 311','2026-08-08',4,''),
(7,'Elena Popov','Individual','Sofia, Bulgaria','Elena Popov','elena.popov@gmail.com','+359 88 555 1276','2026-08-10',3,''),
(8,'Peak Wanderlust Safaris','Travel Agency','Nairobi, Kenya','Brian Otieno','brian@peakwanderlust.co.ke','+254 733 210 406','2026-08-10',2,'Lost on price - revisit with promo rates.'),
(9,'Jonas Berg','Individual','Stockholm, Sweden','Jonas Berg','jonas.berg@telia.se','+46 70 445 8832','2026-08-12',5,''),
(10,'Sarah Whitfield','Individual','London, United Kingdom','Sarah Whitfield','s.whitfield@outlook.com','+44 7700 912233','2026-08-15',2,'Repeat client; February climb in pipeline.'),
(11,'Zinduka Foundation','NGO / Institution','Arusha, Tanzania','Anna Mwakalinga','anna@zindukafoundation.or.tz','+255 754 611 203','2026-08-18',3,''),
(12,'Mark Johnson','Individual','San Francisco, USA','Mark Johnson','mjohnson66@gmail.com','+1 415 290 7788','2026-08-19',5,''),
(13,'Zanzibar Blue Tours','Tour Operator','Zanzibar, Tanzania','Fatma Juma','fatma@zanzibarblue.com','+255 777 340 918','2026-08-20',4,'Strong repeat potential.'),
(14,'Thandiwe Travel','Travel Agency','Cape Town, South Africa','Zanele Dlamini','zanele@thandiwetravel.co.za','+27 82 771 4490','2026-08-22',3,'December load lost; Oct trip completed.'),
(15,'Emily Raison','Individual','Melbourne, Australia','Emily Raison','emily.raison@gmail.com','+61 418 552 903','2026-08-25',2,''),
(16,'Omar Haddad','Individual','Dubai, UAE','Omar Haddad','omar.haddad@gmail.com','+971 50 662 7814','2026-08-27',3,'Tentative Zanzibar hold; weekly updates running.'),
(17,'Hippo Safari Agencies','Travel Agency','Kampala, Uganda','Robert Ssentongo','robert@hipposafari.ug','+256 772 630 471','2026-08-30',5,''),
(18,'Léa Fontaine','Individual','Lyon, France','Léa Fontaine','lea.fontaine@orange.fr','+33 6 12 47 88 90','2026-09-01',4,''),
(19,'Kibo Coffee Co.','Corporate','Arusha, Tanzania','Avash Nyundo','a.nyundo@kibocoffee.co.tz','+255 767 220 540','2026-09-02',3,'Different entity from Arusha Coffee Collective.'),
(20,'Bond Safari & Tours','Travel Agency','Manchester, United Kingdom','Daniel Osei','d.osei@bondsafari.co.uk','+44 7810 554 092','2026-09-05',2,'2 inquiries YTD; 1 confirmed.'),
(21,'Wanjiru Family','Individual','Nairobi, Kenya','Grace Wanjiru','g.wanjiru@yahoo.com','+254 712 908 345','2026-09-06',4,''),
(22,'Kili Trails Adventures','Travel Agency','Moshi, Tanzania','Jacob Lyimo','jacob@kilitrails.co.tz','+255 786 145 072','2026-09-07',4,'Both inquiries converted.'),
(23,'Daniel Schmidt','Individual','Hamburg, Germany','Daniel Schmidt','d.schmidt@freenet.de','+49 151 772 6094','2026-09-08',5,''),
(24,'Nadia Kiprop','Individual','Eldoret, Kenya','Nadia Kiprop','nadia.kiprop@gmail.com','+254 721 440 118','2026-09-11',5,''),
(25,'Ana López','Individual','Barcelona, Spain','Ana López','ana.lopez@correos.es','+34 655 211 038','2026-09-16',3,'Tentative hold for February.'),
(26,'Kenji Tanaka','Individual','Tokyo, Japan','Kenji Tanaka','kenji.tanaka@docomo.jp','+81 90 4412 7883','2026-09-20',2,''),
(27,'Savannah Tracks Ltd','Travel Agency','Atlanta, USA','Paul Ndungu','paul@savannahtracks.com','+254 729 603 771','2026-09-23',3,''),
(28,'David Chen','Individual','Singapore','David Chen','david.chen@gmail.com','+65 8421 8873','2026-09-25',2,''),
(29,'Yara Events Ltd','Corporate','Arusha, Tanzania','Salma Rajabu','s.rajabu@yaraevents.co.tz','+255 753 990 220','2026-09-29',3,'Postponed to 2027; deposit refunded.'),
(30,'Marta Nowak','Individual','Kraków, Poland','Marta Nowak','m.nowak@gmail.com','+48 601 233 771','2026-10-03',2,''),
(31,'Hanna Virtanen','Individual','Helsinki, Finland','Hanna Virtanen','hanna.virtanen@iki.fi','+358 40 552 1963','2026-10-06',5,'');

-- inquiries (id matches the Excel demo INQ-xxx codes)
INSERT INTO inquiries (id,received_date,client_id,contact_person,email,phone,country,client_type,lead_source,tour,travel_date,pax,budget,currency,assigned_to,lead_status,next_follow_up,notes) VALUES
(1,'2026-08-01',1,'Mercy Kariuki','mercy@wildrootstravel.co.ke','+254 722 481 090','Nairobi, Kenya','Travel Agency','Email','Great Migration Safari (5 Days)','2026-08-12',6,19200,'USD',2,'Won',NULL,'Repeat agency client; 3rd inquiry this year.'),
(2,'2026-08-02',2,'Klaus Weber','k.weber@europareisen.de','+49 89 220 4512','Munich, Germany','Travel Agency','Referral','Kilimanjaro Trek - Machame Route (7 Days)','2026-08-20',4,10100,'USD',3,'Won',NULL,'German couple group confirmed at revised price.'),
(3,'2026-08-03',3,'Chiara Rossi','chiara.rossi@libero.it','+39 348 921 6640','Milan, Italy','Individual','Website','Lake Manyara Day Trip',NULL,2,1500,'USD',4,'Open','2026-10-30','Honeymoon; wants private vehicle.'),
(4,'2026-08-03',4,'Hans Müller','hans.mueller@web.de','+49 170 338 2211','Stuttgart, Germany','Individual','Website','Great Migration Safari (5 Days)','2026-09-10',2,5400,'USD',5,'Won',NULL,'Solo couple; river-crossing dates requested.'),
(5,'2026-08-05',5,'Avash Nyundo','a.nyundo@kibocoffee.co.tz','+255 767 220 540','Arusha, Tanzania','Corporate','Referral','Tarangire Day Trip',NULL,12,8000,'USD',4,'Open','2026-11-02','Corporate team-building day trip.'),
(6,'2026-08-08',6,'Baraka Msuya','bookings@serengetigateway.co.tz','+255 784 902 311','Arusha, Tanzania','Travel Agency','Trade Fair','Tarangire & Ngorongoro Safari (4 Days)','2026-09-02',8,23000,'USD',4,'Won',NULL,'Met at Karibu Travel Fair, August.'),
(7,'2026-08-10',7,'Elena Popov','elena.popov@gmail.com','+359 88 555 1276','Sofia, Bulgaria','Individual','Social Media','Lake Manyara Day Trip',NULL,2,1200,'USD',3,'Lost',NULL,'Budget below minimum; declined offer.'),
(8,'2026-08-10',8,'Brian Otieno','brian@peakwanderlust.co.ke','+254 733 210 406','Nairobi, Kenya','Travel Agency','Website','Kilimanjaro Trek - Machame Route (7 Days)',NULL,10,36000,'USD',2,'Lost',NULL,'Lost on price - chose local Moshi operator.'),
(9,'2026-08-12',9,'Jonas Berg','jonas.berg@telia.se','+46 70 445 8832','Stockholm, Sweden','Individual','WhatsApp','Mount Meru Trek (4 Days)',NULL,2,3000,'USD',5,'Inactive',NULL,'No reply to 3 contacts; keep in nurture list.'),
(10,'2026-08-15',10,'Sarah Whitfield','s.whitfield@outlook.com','+44 7700 912233','London, United Kingdom','Individual','Social Media','Kilimanjaro Trek - Machame Route (7 Days)',NULL,2,6000,'USD',2,'Open','2026-11-09','For February 2027; deciding route vs Rongai.'),
(11,'2026-08-18',11,'Anna Mwakalinga','anna@zindukafoundation.or.tz','+255 754 611 203','Arusha, Tanzania','NGO / Institution','Referral','Ngorongoro Crater Day Trip','2026-09-18',12,7200,'USD',3,'Won',NULL,'NGO field trip; invoice to foundation.'),
(12,'2026-08-19',12,'Mark Johnson','mjohnson66@gmail.com','+1 415 290 7788','San Francisco, USA','Individual','Website','Tarangire & Ngorongoro Safari (4 Days)',NULL,4,9000,'USD',5,'Open','2026-11-03','Wants to add Zanzibar extension.'),
(13,'2026-08-20',13,'Fatma Juma','fatma@zanzibarblue.com','+255 777 340 918','Zanzibar, Tanzania','Tour Operator','Email','Zanzibar Beach Holiday (6 Days)','2026-09-22',10,25500,'USD',4,'Won',NULL,'Honeymoon wedding party group.'),
(14,'2026-08-22',14,'Zanele Dlamini','zanele@thandiwetravel.co.za','+27 82 771 4490','Cape Town, South Africa','Travel Agency','Referral','Zanzibar Beach Holiday (6 Days)',NULL,8,26800,'USD',3,'Lost',NULL,'Chose competitor on December dates.'),
(15,'2026-08-25',15,'Emily Raison','emily.raison@gmail.com','+61 418 552 903','Melbourne, Australia','Individual','WhatsApp','Kilimanjaro Trek - Machame Route (7 Days)','2026-10-02',2,5400,'USD',2,'Won',NULL,'Upgraded to 8-day option.'),
(16,'2026-08-27',16,'Omar Haddad','omar.haddad@gmail.com','+971 50 662 7814','Dubai, UAE','Individual','Email','Great Migration Safari (5 Days)',NULL,6,20800,'USD',3,'Open','2026-10-28','Group visas & flights pending; follow-up weekly.'),
(17,'2026-08-28',1,'Mercy Kariuki','mercy@wildrootstravel.co.ke','+254 722 481 090','Nairobi, Kenya','Travel Agency','WhatsApp','Tarangire & Ngorongoro Safari (4 Days)',NULL,14,40500,'USD',4,'Open','2026-10-28','Youth group; fundraising drive underway.'),
(18,'2026-08-30',17,'Robert Ssentongo','robert@hipposafari.ug','+256 772 630 471','Kampala, Uganda','Travel Agency','Trade Fair','Great Migration Safari (5 Days)','2026-10-12',7,22500,'USD',5,'Won',NULL,'Ugandan family; 2 cash payments.'),
(19,'2026-09-01',18,'Léa Fontaine','lea.fontaine@orange.fr','+33 6 12 47 88 90','Lyon, France','Individual','Social Media','Zanzibar Beach Holiday (6 Days)',NULL,2,4300,'USD',4,'Inactive',NULL,'Budget inquiry only; dormant since Sept.'),
(20,'2026-09-02',19,'Avash Nyundo','a.nyundo@kibocoffee.co.tz','+255 767 220 540','Arusha, Tanzania','Corporate','Referral','Tarangire Day Trip',NULL,16,10500,'USD',3,'Open','2026-10-30','Coffee company end-of-year excursion.'),
(21,'2026-09-03',14,'Zanele Dlamini','zanele@thandiwetravel.co.za','+27 82 771 4490','Cape Town, South Africa','Travel Agency','Travel Agency','Tarangire & Ngorongoro Safari (4 Days)','2026-10-14',5,14000,'USD',3,'Won',NULL,'Repeat agency; group of 5 SA guests.'),
(22,'2026-09-05',20,'Daniel Osei','d.osei@bondsafari.co.uk','+44 7810 554 092','Manchester, United Kingdom','Travel Agency','Website','Ngorongoro Crater Day Trip',NULL,12,7900,'USD',2,'Open','2026-10-28','UK corporate party; broad interest.'),
(23,'2026-09-06',21,'Grace Wanjiru','g.wanjiru@yahoo.com','+254 712 908 345','Nairobi, Kenya','Individual','WhatsApp','Great Migration Safari (5 Days)',NULL,3,5200,'USD',4,'Open','2026-11-05','Family group; has not confirmed dates.'),
(24,'2026-09-07',22,'Jacob Lyimo','jacob@kilitrails.co.tz','+255 786 145 072','Moshi, Tanzania','Travel Agency','Referral','Mount Meru Trek (4 Days)','2026-10-19',3,4300,'USD',4,'Won',NULL,'3 friends pre-topping-up Kili climb.'),
(25,'2026-09-08',23,'Daniel Schmidt','d.schmidt@freenet.de','+49 151 772 6094','Hamburg, Germany','Individual','Website','Ngorongoro Crater Day Trip',NULL,4,2600,'USD',5,'Lost',NULL,'Shared group trip unsuitable; declined.'),
(26,'2026-09-09',10,'Sarah Whitfield','s.whitfield@outlook.com','+44 7700 912233','London, United Kingdom','Individual','Email','Kilimanjaro Trek - Machame Route (7 Days)','2026-11-03',2,5600,'USD',2,'Won',NULL,'Repeat client (INQ-010); November climb.'),
(27,'2026-09-10',17,'Robert Ssentongo','robert@hipposafari.ug','+256 772 630 471','Kampala, Uganda','Travel Agency','Email','Zanzibar Beach Holiday (6 Days)',NULL,6,16500,'USD',3,'Inactive',NULL,'Late-January plan; dormant since Sept.'),
(28,'2026-09-11',24,'Nadia Kiprop','nadia.kiprop@gmail.com','+254 721 440 118','Eldoret, Kenya','Individual','Social Media','Mount Meru Trek (4 Days)',NULL,3,4600,'USD',5,'Lost',NULL,'Travel dates moved to 2027 at short notice.'),
(29,'2026-09-12',1,'Mercy Kariuki','mercy@wildrootstravel.co.ke','+254 722 481 090','Nairobi, Kenya','Travel Agency','WhatsApp','Great Migration Safari (5 Days)','2026-11-18',9,27600,'USD',5,'Won',NULL,'Repeat agency; migration river camp.'),
(30,'2026-09-14',16,'Omar Haddad','omar.haddad@gmail.com','+971 50 662 7814','Dubai, UAE','Individual','Website','Zanzibar Beach Holiday (6 Days)','2027-02-03',4,10400,'USD',4,'Open','2026-10-28','Tentative hold (BK-019); decision imminent.'),
(31,'2026-09-16',25,'Ana López','ana.lopez@correos.es','+34 655 211 038','Barcelona, Spain','Individual','Website','Ngorongoro Crater Day Trip',NULL,2,1400,'USD',3,'Open','2026-10-30','Joined shared picnic group - choosing dates.'),
(32,'2026-09-17',20,'Daniel Osei','d.osei@bondsafari.co.uk','+44 7810 554 092','Manchester, United Kingdom','Travel Agency','Email','Zanzibar Beach Holiday (6 Days)','2026-12-01',8,21000,'USD',3,'Won',NULL,'2nd booking this year for Bond Safari.'),
(33,'2026-09-18',13,'Fatma Juma','fatma@zanzibarblue.com','+255 777 340 918','Zanzibar, Tanzania','Tour Operator','Referral','Great Migration Safari (5 Days)',NULL,6,18000,'USD',4,'Lost',NULL,'Partner agency matched cheaper internal rate.'),
(34,'2026-09-20',26,'Kenji Tanaka','kenji.tanaka@docomo.jp','+81 90 4412 7883','Tokyo, Japan','Individual','Website','Tarangire & Ngorongoro Safari (4 Days)',NULL,2,5600,'USD',2,'Open','2026-10-30','Fly-in option requested; awaiting flight tariffs.'),
(35,'2026-09-22',3,'Chiara Rossi','chiara.rossi@libero.it','+39 348 921 6640','Milan, Italy','Individual','Website','Tarangire & Ngorongoro Safari (4 Days)','2026-12-05',4,11400,'USD',4,'Won',NULL,'Early-Christmas family trip in December.'),
(36,'2026-09-23',27,'Paul Ndungu','paul@savannahtracks.com','+254 729 603 771','Atlanta, USA','Travel Agency','Trade Fair','Great Migration Safari (5 Days)',NULL,12,31500,'USD',3,'Lost',NULL,'US corp travel tendered to DMC in-house.'),
(37,'2026-09-24',1,'Mercy Kariuki','mercy@wildrootstravel.co.ke','+254 722 481 090','Nairobi, Kenya','Travel Agency','WhatsApp','Kilimanjaro Trek - Machame Route (7 Days)',NULL,8,22500,'USD',5,'Open','2026-10-29','8-person group collecting deposits.'),
(38,'2026-09-25',28,'David Chen','david.chen@gmail.com','+65 8421 8873','Singapore','Individual','Referral','Great Migration Safari (5 Days)','2026-12-15',2,5500,'USD',2,'Won',NULL,'Referral from INQ-026 guests; honeymoon package.'),
(39,'2026-09-28',6,'Baraka Msuya','bookings@serengetigateway.co.tz','+255 784 902 311','Arusha, Tanzania','Travel Agency','Email','Mount Meru Trek (4 Days)',NULL,6,8900,'USD',4,'Open','2026-10-31','Repeat agency; evaluating February window.'),
(40,'2026-09-29',29,'Salma Rajabu','s.rajabu@yaraevents.co.tz','+255 753 990 220','Arusha, Tanzania','Corporate','Referral','Tarangire Day Trip','2026-11-08',15,10000,'USD',3,'Lost',NULL,'Client event postponed; booking cancelled.'),
(41,'2026-10-01',22,'Jacob Lyimo','jacob@kilitrails.co.tz','+255 786 145 072','Moshi, Tanzania','Travel Agency','WhatsApp','Mount Meru Trek (4 Days)','2027-01-08',4,5500,'USD',5,'Won',NULL,'Repeat agency; 4 climbing guides needed.'),
(42,'2026-10-03',30,'Marta Nowak','m.nowak@gmail.com','+48 601 233 771','Kraków, Poland','Individual','Social Media','Lake Manyara Day Trip',NULL,2,1500,'USD',2,'Open','2026-10-28','Instagram enquiry; replies slow.'),
(43,'2026-10-06',31,'Hanna Virtanen','hanna.virtanen@iki.fi','+358 40 552 1963','Helsinki, Finland','Individual','Website','Tarangire & Ngorongoro Safari (4 Days)','2027-02-25',3,6800,'USD',5,'Open','2026-10-28','Tentative hold (BK-018); awaiting visas.'),
(44,'2026-10-08',2,'Klaus Weber','k.weber@europareisen.de','+49 89 220 4512','Munich, Germany','Travel Agency','Referral','Kilimanjaro Trek - Machame Route (7 Days)','2027-01-15',6,15000,'USD',3,'Open','2026-10-29','EuropaReisen January 2027 charter group.'),
(45,'2026-10-09',6,'Baraka Msuya','bookings@serengetigateway.co.tz','+255 784 902 311','Arusha, Tanzania','Travel Agency','Website','Great Migration Safari (5 Days)',NULL,10,27000,'USD',3,'Open','2026-10-31','Repeat agency; 10 pax, February.');

-- opportunities (weighted value computed in app)
INSERT INTO opportunities (id,inquiry_id,sales_stage,opened_date,expected_close,estimated_value,currency,probability,assigned_to,last_contact,next_follow_up,lead_status,lost_reason) VALUES
(1,1,'Confirmed','2026-08-01','2027-01-31',18400,'USD',100,2,'2026-08-16',NULL,'Won',''),
(2,2,'Confirmed','2026-08-02','2027-01-31',9800,'USD',100,3,'2026-08-27',NULL,'Won',''),
(3,3,'New Inquiry','2026-08-03','2027-01-31',1400,'USD',15,4,'2026-10-24','2026-10-30','Open',''),
(4,4,'Confirmed','2026-08-03','2027-01-31',5200,'USD',100,5,'2026-09-15',NULL,'Won',''),
(5,5,'Follow-Up','2026-08-05','2027-01-31',7600,'USD',50,4,'2026-10-22','2026-11-02','Open',''),
(6,6,'Confirmed','2026-08-08','2027-01-31',22400,'USD',100,4,'2026-09-06',NULL,'Won',''),
(7,7,'Lost','2026-08-10','2027-01-31',1100,'USD',0,3,'2026-09-04',NULL,'Lost','Budget below minimum; declined offer.'),
(8,8,'Lost','2026-08-10','2027-01-31',34200,'USD',0,2,'2026-09-02',NULL,'Lost','Lost on price - chose local Moshi operator.'),
(9,9,'Inactive','2026-08-12','2027-01-31',2850,'USD',5,5,'2026-09-05',NULL,'Inactive',''),
(10,10,'Quotation Sent','2026-08-15','2027-01-31',5700,'USD',45,2,'2026-10-25','2026-11-09','Open',''),
(11,11,'Confirmed','2026-08-18','2027-01-31',6900,'USD',100,3,'2026-09-19',NULL,'Won',''),
(12,12,'New Inquiry','2026-08-19','2027-01-31',8550,'USD',15,5,'2026-10-23','2026-11-03','Open',''),
(13,13,'Confirmed','2026-08-20','2027-01-31',24800,'USD',100,4,'2026-09-25',NULL,'Won',''),
(14,14,'Lost','2026-08-22','2027-01-31',25450,'USD',0,3,'2026-09-01',NULL,'Lost','Chose competitor on December dates.'),
(15,15,'Confirmed','2026-08-25','2027-01-31',5200,'USD',100,2,'2026-10-08',NULL,'Won',''),
(16,16,'Negotiation','2026-08-27','2027-01-31',19750,'USD',65,3,'2026-10-20','2026-10-28','Open',''),
(17,17,'Follow-Up','2026-08-28','2027-01-31',38450,'USD',50,4,'2026-10-20','2026-10-28','Open',''),
(18,18,'Confirmed','2026-08-30','2027-01-31',21800,'USD',100,5,'2026-10-17',NULL,'Won',''),
(19,19,'Inactive','2026-09-01','2027-01-31',4050,'USD',5,4,'2026-09-12',NULL,'Inactive',''),
(20,20,'Qualified','2026-09-02','2027-01-31',9950,'USD',30,3,'2026-10-24','2026-10-30','Open',''),
(21,21,'Confirmed','2026-09-03','2027-01-31',13500,'USD',100,3,'2026-10-15',NULL,'Won',''),
(22,22,'Qualified','2026-09-05','2027-01-31',7500,'USD',30,2,'2026-10-21','2026-10-28','Open',''),
(23,23,'New Inquiry','2026-09-06','2027-01-31',4900,'USD',15,4,'2026-10-24','2026-11-05','Open',''),
(24,24,'Confirmed','2026-09-07','2027-01-31',4100,'USD',100,4,'2026-10-21',NULL,'Won',''),
(25,25,'Lost','2026-09-08','2027-01-31',2450,'USD',0,5,'2026-09-23',NULL,'Lost','Shared group trip unsuitable; declined.'),
(26,26,'Confirmed','2026-09-09','2027-01-31',5450,'USD',100,2,'2026-10-07',NULL,'Won',''),
(27,27,'Inactive','2026-09-10','2027-01-31',15650,'USD',5,3,'2026-09-22',NULL,'Inactive',''),
(28,28,'Lost','2026-09-11','2027-01-31',4350,'USD',0,5,'2026-09-14',NULL,'Lost','Travel dates moved to 2027 at short notice.'),
(29,29,'Confirmed','2026-09-12','2027-01-31',27200,'USD',100,5,'2026-10-12',NULL,'Won',''),
(30,30,'Negotiation','2026-09-14','2026-10-28',9850,'USD',60,4,'2026-10-21','2026-10-28','Open',''),
(31,31,'New Inquiry','2026-09-16','2027-01-31',1300,'USD',15,3,'2026-10-23','2026-10-30','Open',''),
(32,32,'Confirmed','2026-09-17','2027-01-31',20500,'USD',100,3,'2026-10-19',NULL,'Won',''),
(33,33,'Lost','2026-09-18','2027-01-31',17100,'USD',0,4,'2026-09-30',NULL,'Lost','Partner agency matched cheaper internal rate.'),
(34,34,'Follow-Up','2026-09-20','2027-01-31',5300,'USD',55,2,'2026-10-21','2026-10-30','Open',''),
(35,35,'Confirmed','2026-09-22','2027-01-31',11000,'USD',100,4,'2026-10-18',NULL,'Won',''),
(36,36,'Lost','2026-09-23','2027-01-31',29900,'USD',0,3,'2026-10-06',NULL,'Lost','US corp travel tendered to DMC in-house.'),
(37,37,'Follow-Up','2026-09-24','2027-01-31',21350,'USD',50,5,'2026-10-23','2026-10-29','Open',''),
(38,38,'Confirmed','2026-09-25','2027-01-31',5350,'USD',100,2,'2026-10-21',NULL,'Won',''),
(39,39,'Qualified','2026-09-28','2027-01-31',8450,'USD',30,4,'2026-10-20','2026-10-31','Open',''),
(40,40,'Lost','2026-09-29','2027-01-31',9500,'USD',0,3,'2026-10-10',NULL,'Lost','Client event postponed; booking cancelled.'),
(41,41,'Confirmed','2026-10-01','2027-01-31',5300,'USD',100,5,'2026-10-24',NULL,'Won',''),
(42,42,'New Inquiry','2026-10-03','2027-01-31',1400,'USD',15,2,'2026-10-25','2026-10-28','Open',''),
(43,43,'Negotiation','2026-10-06','2026-10-28',6450,'USD',60,5,'2026-10-24','2026-10-28','Open',''),
(44,44,'Negotiation','2026-10-08','2027-01-31',14250,'USD',75,3,'2026-10-24','2026-10-29','Open',''),
(45,45,'New Inquiry','2026-10-09','2027-01-31',25650,'USD',15,3,'2026-10-20','2026-10-31','Open','');

-- quotations
INSERT INTO quotations (id,inquiry_id,booking_id,quote_date,valid_until,tour,pax,amount,currency,status,prepared_by,version,notes) VALUES
(1,1,1,'2026-08-02','2026-08-23','Great Migration Safari (5 Days)',6,18400,'USD','Accepted',2,2,'Accepted; converted to booking.'),
(2,2,2,'2026-08-03','2026-08-24','Kilimanjaro Trek - Machame Route (7 Days)',4,9800,'USD','Accepted',3,2,'Accepted; converted to booking.'),
(3,4,4,'2026-08-19','2026-09-09','Great Migration Safari (5 Days)',2,5200,'USD','Accepted',5,2,'Accepted; converted to booking.'),
(4,6,3,'2026-08-15','2026-09-05','Tarangire & Ngorongoro Safari (4 Days)',8,22400,'USD','Accepted',4,2,'Accepted; converted to booking.'),
(5,11,5,'2026-08-28','2026-09-18','Ngorongoro Crater Day Trip',12,6900,'USD','Accepted',3,2,'Accepted; converted to booking.'),
(6,13,6,'2026-08-27','2026-09-17','Zanzibar Beach Holiday (6 Days)',10,24800,'USD','Accepted',4,2,'Accepted; converted to booking.'),
(7,15,7,'2026-09-06','2026-09-27','Kilimanjaro Trek - Machame Route (7 Days)',2,5200,'USD','Accepted',2,2,'Accepted; converted to booking.'),
(8,18,8,'2026-09-14','2026-10-05','Great Migration Safari (5 Days)',7,21800,'USD','Accepted',5,2,'Accepted; converted to booking.'),
(9,21,9,'2026-09-23','2026-10-14','Tarangire & Ngorongoro Safari (4 Days)',5,13500,'USD','Accepted',3,2,'Accepted; converted to booking.'),
(10,24,10,'2026-10-02','2026-10-23','Mount Meru Trek (4 Days)',3,4100,'USD','Accepted',4,2,'Accepted; converted to booking.'),
(11,26,11,'2026-10-03','2026-10-24','Kilimanjaro Trek - Machame Route (7 Days)',2,5450,'USD','Accepted',2,2,'Accepted; converted to booking.'),
(12,29,12,'2026-10-05','2026-10-26','Great Migration Safari (5 Days)',9,27200,'USD','Accepted',5,2,'Accepted; converted to booking.'),
(13,32,13,'2026-10-10','2026-10-31','Zanzibar Beach Holiday (6 Days)',8,20500,'USD','Accepted',3,2,'Accepted; converted to booking.'),
(14,35,14,'2026-10-12','2026-11-02','Tarangire & Ngorongoro Safari (4 Days)',4,11000,'USD','Accepted',4,2,'Accepted; converted to booking.'),
(15,38,15,'2026-10-16','2026-11-06','Great Migration Safari (5 Days)',2,5350,'USD','Accepted',2,2,'Accepted; converted to booking.'),
(16,41,16,'2026-10-20','2026-11-10','Mount Meru Trek (4 Days)',4,5300,'USD','Accepted',5,2,'Accepted; converted to booking.'),
(17,30,NULL,'2026-09-20','2026-10-11','Zanzibar Beach Holiday (6 Days)',4,10200,'USD','Sent',4,1,'Awaiting client decision.'),
(18,43,NULL,'2026-10-12','2026-11-02','Tarangire & Ngorongoro Safari (4 Days)',3,6600,'USD','Sent',5,1,'Awaiting client decision.'),
(19,10,NULL,'2026-08-21','2026-09-11','Kilimanjaro Trek - Machame Route (7 Days)',2,6000,'USD','Sent',2,1,'Awaiting client decision.'),
(20,16,NULL,'2026-09-02','2026-09-23','Great Migration Safari (5 Days)',6,20800,'USD','Sent',3,1,'Awaiting client decision.'),
(21,37,NULL,'2026-09-30','2026-10-21','Kilimanjaro Trek - Machame Route (7 Days)',8,22200,'USD','Sent',5,1,'Awaiting client decision.'),
(22,44,NULL,'2026-10-20','2026-11-10','Kilimanjaro Trek - Machame Route (7 Days)',6,14400,'USD','Revised',3,2,'Revised route & pricing 16 Oct.'),
(23,22,NULL,'2026-10-22','2026-11-12','Ngorongoro Crater Day Trip',12,7700,'USD','Draft',2,1,'Draft pending internal cost check.'),
(24,8,NULL,'2026-08-20','2026-09-10','Kilimanjaro Trek - Machame Route (7 Days)',10,34500,'USD','Rejected',2,1,'Rejected on price.'),
(25,14,NULL,'2026-09-01','2026-09-22','Zanzibar Beach Holiday (6 Days)',8,26000,'USD','Rejected',3,1,'Rejected on price.'),
(26,33,NULL,'2026-09-28','2026-10-19','Great Migration Safari (5 Days)',6,17300,'USD','Rejected',4,1,'Rejected on price.'),
(27,36,NULL,'2026-10-03','2026-10-24','Great Migration Safari (5 Days)',12,30500,'USD','Rejected',3,1,'Rejected on price.'),
(28,7,NULL,'2026-08-18','2026-09-08','Lake Manyara Day Trip',2,1150,'USD','Expired',3,1,'Quote lapsed without response.'),
(29,25,NULL,'2026-09-16','2026-10-07','Ngorongoro Crater Day Trip',4,2500,'USD','Expired',5,1,'Quote lapsed without response.'),
(30,28,NULL,'2026-09-19','2026-10-10','Mount Meru Trek (4 Days)',3,4400,'USD','Expired',5,1,'Quote lapsed without response.');

-- bookings (id matches the Excel demo BK-xxx codes)
INSERT INTO bookings (id,inquiry_id,client_id,tour,arrival,departure,pax,status,booking_date,salesperson,booking_value,currency,actual_revenue,direct_cost,payment_due_date,notes) VALUES
(1,1,1,'Great Migration Safari (5 Days)','2026-08-12','2026-08-16',6,'Completed','2026-08-03',2,18400,'USD',18400,12600,'2026-08-06','3x1 deposits; balance due pre-arrival.'),
(2,2,2,'Kilimanjaro Trek - Machame Route (7 Days)','2026-08-20','2026-08-26',4,'Completed','2026-08-05',3,9800,'USD',9800,6900,'2026-08-10','Direct with guests.'),
(3,6,6,'Tarangire & Ngorongoro Safari (4 Days)','2026-09-02','2026-09-05',8,'Completed','2026-08-18',4,22400,'USD',22400,15200,'2026-08-22','2 vehicles + pop-up roof.'),
(4,4,4,'Great Migration Safari (5 Days)','2026-09-10','2026-09-14',2,'Completed','2026-08-20',5,5200,'USD',5200,3550,'2026-08-25','Migration season premium applied.'),
(5,11,11,'Ngorongoro Crater Day Trip','2026-09-18','2026-09-18',12,'Completed','2026-09-01',3,6900,'USD',6900,4700,'2026-09-08','Field-trip invoice via foundation.'),
(6,13,13,'Zanzibar Beach Holiday (6 Days)','2026-09-22','2026-09-28',10,'Completed','2026-09-02',4,24800,'USD',24800,16800,'2026-09-10','Wedding party; seafront resort.'),
(7,15,15,'Kilimanjaro Trek - Machame Route (7 Days)','2026-10-02','2026-10-08',2,'Completed','2026-09-10',2,5200,'USD',5200,3620,'2026-09-15','Acclimatisation day in Moshi.'),
(8,18,17,'Great Migration Safari (5 Days)','2026-10-12','2026-10-16',7,'Completed','2026-09-18',5,21800,'USD',21800,14900,'2026-09-25','Family group, mobile money payments.'),
(9,21,14,'Tarangire & Ngorongoro Safari (4 Days)','2026-10-14','2026-10-17',5,'Completed','2026-09-25',3,13500,'USD',13500,9200,'2026-10-02','Repeat agency; 5% rebate applied.'),
(10,24,22,'Mount Meru Trek (4 Days)','2026-10-19','2026-10-22',3,'Completed','2026-10-05',4,4100,'USD',4100,2800,'2026-10-12','Guides booked via Moshi office.'),
(11,26,10,'Kilimanjaro Trek - Machame Route (7 Days)','2026-11-03','2026-11-09',2,'Confirmed','2026-10-06',2,5450,'USD',5450,3700,'2026-10-20','Repeat individual client.'),
(12,29,1,'Great Migration Safari (5 Days)','2026-11-18','2026-11-22',9,'Confirmed','2026-10-09',5,27200,'USD',27200,18400,'2026-10-25','Awaiting deposit - chase weekly.'),
(13,32,20,'Zanzibar Beach Holiday (6 Days)','2026-12-01','2026-12-07',8,'Confirmed','2026-10-14',3,20500,'USD',20500,13900,'2026-11-05','Corporate gift-drive extension added.'),
(14,35,3,'Tarangire & Ngorongoro Safari (4 Days)','2026-12-05','2026-12-08',4,'Confirmed','2026-10-16',4,11000,'USD',11000,7500,'2026-11-15','Full prepayment received at booking.'),
(15,38,28,'Great Migration Safari (5 Days)','2026-12-15','2026-12-19',2,'Confirmed','2026-10-20',2,5350,'USD',5350,3650,'2026-11-20','Honeymoon; room upgrade negotiated.'),
(16,41,22,'Mount Meru Trek (4 Days)','2027-01-08','2027-01-11',4,'Confirmed','2026-10-23',5,5300,'USD',5300,3600,'2026-12-01','4 guides + porters allocated.'),
(17,44,2,'Kilimanjaro Trek - Machame Route (7 Days)','2027-01-15','2027-01-21',6,'In Progress','2026-10-22',3,14700,'USD',NULL,NULL,'2026-12-15','Awaiting internal flights confirmation.'),
(18,43,31,'Tarangire & Ngorongoro Safari (4 Days)','2027-02-25','2027-02-28',3,'Tentative','2026-10-12',5,6800,'USD',NULL,NULL,NULL,'Hold placed 14 days; visas pending.'),
(19,30,16,'Zanzibar Beach Holiday (6 Days)','2027-02-03','2027-02-09',4,'Tentative','2026-09-28',4,10400,'USD',NULL,NULL,NULL,'Decision expected before Oct 31.'),
(20,40,29,'Tarangire Day Trip','2026-11-08','2026-11-08',15,'Cancelled','2026-09-30',3,10000,'USD',NULL,NULL,'2026-10-25','Postponed to 2027; deposit refunded.');

-- payment schedule
INSERT INTO payment_schedule (id,booking_id,payment_type,description,due_date,amount_due,date_paid,amount_paid) VALUES
(1,1,'Deposit','Group deposit 50%','2026-08-05',9200,'2026-08-05',9200),
(2,1,'Installment','Second installment','2026-08-08',6000,'2026-08-08',6000),
(3,1,'Final Payment','Balance due before arrival','2026-08-10',3200,NULL,NULL),
(4,2,'Deposit','Booking deposit','2026-08-06',4000,'2026-08-06',4000),
(5,2,'Final Payment','Balance due before climb','2026-08-10',5800,'2026-08-18',5800),
(6,3,'Deposit','Group deposit','2026-08-20',8000,'2026-08-20',8000),
(7,3,'Final Payment','Balance due before arrival','2026-08-22',14400,'2026-09-01',14400),
(8,4,'Deposit','Booking deposit','2026-08-22',2000,'2026-08-22',2000),
(9,4,'Final Payment','Balance due before arrival','2026-08-25',3200,'2026-09-09',3200),
(10,5,'Final Payment','Single full payment (NGO)','2026-09-08',6900,'2026-09-05',6900),
(11,6,'Deposit','Group deposit','2026-09-06',10000,'2026-09-06',10000),
(12,6,'Final Payment','Balance due before arrival','2026-09-10',14800,'2026-09-16',14800),
(13,7,'Deposit','Booking deposit','2026-09-11',500,'2026-09-11',500),
(14,7,'Final Payment','Balance (flights + permits)','2026-09-15',4700,'2026-10-01',4700),
(15,8,'Deposit','Family group deposit','2026-09-20',8000,'2026-09-20',8000),
(16,8,'Final Payment','Balance due before arrival','2026-09-25',13800,'2026-10-05',13800),
(17,9,'Deposit','Agency deposit','2026-09-27',5000,'2026-09-27',5000),
(18,9,'Final Payment','Balance due before arrival','2026-10-02',8500,'2026-10-09',8500),
(19,10,'Final Payment','Single full payment','2026-10-12',4100,'2026-10-10',4100),
(20,11,'Deposit','Booking deposit','2026-10-09',2500,'2026-10-09',2500),
(21,11,'Final Payment','Balance due before climb','2026-10-20',2950,NULL,NULL),
(22,12,'Final Payment','Balance / full prepayment','2026-10-25',27200,NULL,NULL),
(23,13,'Deposit','Deposit 40%','2026-10-18',8200,'2026-10-18',8200),
(24,13,'Final Payment','Balance due before arrival','2026-11-05',12300,NULL,NULL),
(25,14,'Final Payment','Full prepayment at booking','2026-11-15',11000,'2026-10-20',11000),
(26,15,'Deposit','Honeymoon deposit','2026-10-21',2000,'2026-10-21',2000),
(27,15,'Final Payment','Balance due before arrival','2026-11-20',3350,NULL,NULL),
(28,16,'Deposit','Agency deposit','2026-10-24',2000,'2026-10-24',2000),
(29,16,'Final Payment','Balance due before trek','2026-12-01',3300,NULL,NULL),
(30,17,'Deposit','Charter deposit','2026-10-23',3000,'2026-10-23',3000),
(31,17,'Final Payment','Balance due before arrival','2026-12-15',11700,NULL,NULL);

-- payments
INSERT INTO payments (id,booking_id,invoice_ref,payment_date,payment_type,amount,currency,method,received_by,notes) VALUES
(1,1,'WR-INV-1041','2026-08-05','Deposit',9200,'USD','Bank Transfer',2,'50% group deposit'),
(2,1,'WR-INV-1042','2026-08-08','Installment',6000,'USD','Mobile Money',3,''),
(3,2,'PAY-3319','2026-08-06','Deposit',4000,'USD','Card',5,''),
(4,2,'PAY-3320','2026-08-18','Final Payment',5800,'USD','Bank Transfer',2,''),
(5,3,'SG-INV-0215','2026-08-20','Deposit',8000,'USD','Bank Transfer',4,''),
(6,3,'SG-INV-0216','2026-09-01','Final Payment',14400,'USD','Bank Transfer',3,''),
(7,4,'HM-88231','2026-08-22','Deposit',2000,'USD','Online Gateway',5,''),
(8,4,'HM-88232','2026-09-09','Final Payment',3200,'USD','Card',2,''),
(9,5,'ZF-INV-7701','2026-09-05','Final Payment',6900,'USD','Mobile Money',3,''),
(10,6,'ZBT-INV-3309','2026-09-06','Deposit',10000,'USD','Bank Transfer',4,''),
(11,6,'ZBT-INV-3310','2026-09-16','Final Payment',14800,'USD','Bank Transfer',3,''),
(12,7,'ERC-99012','2026-09-11','Deposit',500,'USD','Card',2,'Booking deposit'),
(13,7,'ERC-99013','2026-10-01','Final Payment',4700,'USD','Online Gateway',5,''),
(14,8,'HIP-INV-01L','2026-09-20','Deposit',8000,'USD','Bank Transfer',5,''),
(15,8,'HIP-INV-01M','2026-10-05','Final Payment',13800,'USD','Bank Transfer',4,''),
(16,9,'TT-INV-4410','2026-09-27','Deposit',5000,'USD','Bank Transfer',3,''),
(17,9,'TT-INV-4410B','2026-10-09','Final Payment',8500,'USD','Mobile Money',5,''),
(18,10,'KT-INV-3390','2026-10-10','Final Payment',4100,'USD','Mobile Money',4,'Paid in full at booking'),
(19,11,'SW-74120','2026-10-09','Deposit',2500,'USD','Card',2,''),
(20,13,'BS-INV-1102','2026-10-18','Deposit',8200,'USD','Online Gateway',3,'40% deposit'),
(21,14,'CR-56201','2026-10-20','Final Payment',11000,'USD','Bank Transfer',4,'Full prepayment'),
(22,15,'DC-10112','2026-10-21','Deposit',2000,'USD','Mobile Money',2,''),
(23,16,'KT-INV-3421','2026-10-24','Deposit',2000,'USD','Bank Transfer',5,''),
(24,17,'ER-INV-0061','2026-10-23','Deposit',3000,'USD','Bank Transfer',3,'Initial charter deposit'),
(25,20,'YE-INV-0091','2026-10-08','Deposit',1200,'USD','Mobile Money',3,'Event deposit'),
(26,20,'YE-RFD-0091','2026-10-15','Refund',-1200,'USD','Bank Transfer',3,'Deposit refunded after cancellation');

-- follow-ups
INSERT INTO follow_ups (id,inquiry_id,action,due_date,priority,assigned_to,status,completed_date,notes) VALUES
(1,1,'Send booking confirmation & group deposit invoice.','2026-08-04','High',2,'Completed','2026-08-04','Deposit received; booking confirmed.'),
(2,2,'Collect final headcount & dietary notes.','2026-08-06','High',3,'Completed','2026-08-06',''),
(3,6,'Prepare travel fair post-visit proposal.','2026-08-20','Medium',4,'Completed','2026-08-20','Quote accepted.'),
(4,4,'Confirm river-crossing itinerary dates.','2026-08-25','Medium',5,'Completed','2026-08-25','Dates confirmed.'),
(5,13,'Final payment schedule to Zanzibar Blue.','2026-08-29','Medium',4,'Completed','2026-08-29',''),
(6,11,'Issue invoice to foundation finance.','2026-09-04','Medium',3,'Completed','2026-09-04',''),
(7,7,'Send revised budget option.','2026-09-08','Low',3,'Completed','2026-09-08','Client declined; budget constraint.'),
(8,15,'Confirm gear list & acclimatisation day.','2026-09-14','High',2,'Completed','2026-09-14',''),
(9,21,'Send SA group arrival logistics.','2026-09-30','Medium',3,'Completed','2026-09-30',''),
(10,18,'Verify vehicle allocation for family group.','2026-10-01','Medium',5,'Completed','2026-10-01',''),
(11,24,'Allocate guides & porters for Meru trek.','2026-10-08','High',4,'Completed','2026-10-08',''),
(12,29,'Issue Wild Roots group deposit invoice.','2026-10-08','High',5,'Completed','2026-10-08','Deposit invoice sent.'),
(13,25,'Close out expired quote on shared option.','2026-10-09','Low',5,'Completed','2026-10-09','Marked lost this week.'),
(14,32,'Collect 40% deposit for December stay.','2026-10-15','High',3,'Completed','2026-10-15','Deposit received 18 Oct.'),
(15,16,'Follow up group size decision & flight dates.','2026-10-08','High',3,'In Progress',NULL,'Third reminder sent; response pending.'),
(16,37,'Chase group deposits for February climb.','2026-10-09','Medium',5,'Pending',NULL,''),
(17,34,'Send fly-in tariff comparison.','2026-10-12','Medium',2,'Pending',NULL,''),
(18,17,'Call coordinator about fundraising result.','2026-10-15','High',4,'In Progress',NULL,''),
(19,39,'Share February availability calendar.','2026-10-19','Medium',4,'Pending',NULL,''),
(20,42,'Follow up on Lake Manyara date choice.','2026-10-21','Medium',2,'Pending',NULL,''),
(21,45,'Send February migration pricing sheet.','2026-10-22','High',3,'Pending',NULL,''),
(22,22,'Prepare January departure quotation.','2026-10-28','High',2,'Pending',NULL,''),
(23,31,'Confirm shared-group join dates.','2026-10-30','Medium',3,'Pending',NULL,''),
(24,12,'Quote Zanzibar extension option.','2026-11-03','Medium',5,'Pending',NULL,''),
(25,23,'Second call to agree travel window.','2026-11-05','Low',4,'Pending',NULL,''),
(26,10,'Follow up on February proposal.','2026-11-09','Medium',2,'Pending',NULL,''),
(27,40,'Cancellation admin & refund filing.','2026-10-10','Medium',3,'Cancelled',NULL,'Event postponed; deposit refunded.');

-- sales targets
INSERT INTO sales_targets (id,month,year,salesperson,target_revenue,notes) VALUES
(1,8,2026,2,18000,'High season ramp-up.'),
(2,8,2026,3,12000,'High season ramp-up.'),
(3,8,2026,4,21000,'High season ramp-up.'),
(4,8,2026,5,6500,'High season ramp-up.'),
(5,9,2026,2,6000,'Peak migration demand.'),
(6,9,2026,3,19000,'Peak migration demand.'),
(7,9,2026,4,26500,'Peak migration demand.'),
(8,9,2026,5,20000,'Peak migration demand.'),
(9,10,2026,2,12000,'Strong Zanzibar + Kilimanjaro pipeline.'),
(10,10,2026,3,24000,'Strong Zanzibar + Kilimanjaro pipeline.'),
(11,10,2026,4,16000,'Strong Zanzibar + Kilimanjaro pipeline.'),
(12,10,2026,5,34000,'Strong Zanzibar + Kilimanjaro pipeline.');

-- activities
INSERT INTO activities (id,activity_date,inquiry_id,activity_type,summary,next_action,next_action_date,salesperson,duration_min) VALUES
(1,'2026-08-02',1,'Email','Sent migration itinerary + prices.','Await reply','2026-08-04',2,25),
(2,'2026-08-05',2,'WhatsApp','Shared Machame route guide & packing list.','Send final headcount form','2026-08-08',3,20),
(3,'2026-08-08',4,'Call','Discussed river-crossing camp preferences.','Email confirmation letter','2026-08-10',5,18),
(4,'2026-08-12',6,'Meeting','Karibu Travel Fair stand meeting with agency.','Send trading proposal','2026-08-16',4,45),
(5,'2026-08-15',10,'Email','Sent route comparison offer (Machame vs Rongai).','Second email in a week','2026-09-01',2,30),
(6,'2026-08-17',11,'WhatsApp','Confirmed field-trip date with foundation.','Issue invoice','2026-09-01',3,15),
(7,'2026-08-21',13,'Call','Reviewed resort upgrade options for Zanzibar.','Book seafront block','2026-08-24',4,35),
(8,'2026-08-28',17,'WhatsApp','Discussed youth-group fundraising timeline.','Send batch quote incl. own kit','2026-09-05',4,22),
(9,'2026-09-02',20,'Email','Coffee company year-end excursion options.','Schedule site visit','2026-09-12',3,30),
(10,'2026-09-05',22,'Email','Sent Ngorongoro day-trip options to UK group.','Await shortlist','2026-09-18',2,20),
(11,'2026-09-11',26,'Call','Repeat client - moved climb to November.','Send booking contract','2026-09-16',2,25),
(12,'2026-09-14',30,'Meeting','Omar tentative hold; deposit deadline set.','Chase decision','2026-10-20',4,40),
(13,'2026-09-17',32,'Email','Bond Safari December proposal finalised.','Collect 40% deposit','2026-10-10',3,30),
(14,'2026-09-19',35,'Call','Walked family through December itinerary.','Send confirmable dates','2026-10-01',4,28),
(15,'2026-09-23',38,'WhatsApp','Honeymoon - shared migration lodge options.','Confirm balloon ride addition','2026-10-03',2,20),
(16,'2026-09-26',41,'Call','Kili Trails - allocated guides for Meru.','Send crew cost sheet','2026-10-05',5,18),
(17,'2026-09-30',43,'Meeting','Virtual meeting - itinerary walkthrough.','Hold pax & rooms for 14 days','2026-10-12',5,45),
(18,'2026-10-03',44,'Email','EuropaReisen January charter - cost sheet shared.','Revise if flights change','2026-10-16',3,35),
(19,'2026-10-06',45,'Email','February migration quote themes for 10 pax.','Send detailed pricing','2026-10-13',3,26),
(20,'2026-10-09',42,'WhatsApp','Marta - Lake Manyara day trip follow-up.','Confirm date in October','2026-10-28',2,12),
(21,'2026-10-13',31,'Call','Ana - shared picnic group join options.','Send joinable dates list','2026-10-30',3,15),
(22,'2026-10-16',37,'Call','Wild Roots - deposits chase for group of 8.','Escalate if unpaid by 25 Oct','2026-10-26',5,22),
(23,'2026-10-20',34,'Email','Kenji - fly-in tariff comparison document.','Follow up in 3 days','2026-10-30',2,30),
(24,'2026-10-24',22,'Email','Bond Safari - gift-drive extension idea.','Draft January quote','2026-10-28',2,25);

SET FOREIGN_KEY_CHECKS = 1;
