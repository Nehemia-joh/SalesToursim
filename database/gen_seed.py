"""Generate seed.sql for the WebVersion PHP app — same 3-month demo dataset
as the Excel workbook (Aug-Oct 2026). Run:  python3 gen_seed.py"""
import hashlib, os, datetime as dt
D = dt.date

# ------------------------------------------------------------------ team
TEAM = ["Kelvin Mtui", "Amina Hassan", "Joseph Mkumbo", "Grace Mushi"]
EMAILS = {
    "Kelvin Mtui": "kelvin.mtui@michaeltourism.com",
    "Amina Hassan": "amina.hassan@michaeltourism.com",
    "Joseph Mkumbo": "joseph.mkumbo@michaeltourism.com",
    "Grace Mushi": "grace.mushi@michaeltourism.com",
}
DEMO_PASSWORD = "demo1234"

def phash(password):
    salt = os.urandom(16).hex()[:32]
    return salt, hashlib.sha256((salt + password).encode()).hexdigest()

# ------------------------------------------------------------------ client roster (per inquiry idx)
CLIENTS = {
    1:  ("Wild Roots Travel Ltd", "Mercy Kariuki", "mercy@wildrootstravel.co.ke", "+254 722 481 090", "Nairobi, Kenya", "Travel Agency"),
    2:  ("EuropaReisen GmbH", "Klaus Weber", "k.weber@europareisen.de", "+49 89 220 4512", "Munich, Germany", "Travel Agency"),
    3:  ("Chiara Rossi", "Chiara Rossi", "chiara.rossi@libero.it", "+39 348 921 6640", "Milan, Italy", "Individual"),
    4:  ("Hans Müller", "Hans Müller", "hans.mueller@web.de", "+49 170 338 2211", "Stuttgart, Germany", "Individual"),
    5:  ("Arusha Coffee Collective", "Avash Nyundo", "a.nyundo@kibocoffee.co.tz", "+255 767 220 540", "Arusha, Tanzania", "Corporate"),
    6:  ("Serengeti Gateway Ltd", "Baraka Msuya", "bookings@serengetigateway.co.tz", "+255 784 902 311", "Arusha, Tanzania", "Travel Agency"),
    7:  ("Elena Popov", "Elena Popov", "elena.popov@gmail.com", "+359 88 555 1276", "Sofia, Bulgaria", "Individual"),
    8:  ("Peak Wanderlust Safaris", "Brian Otieno", "brian@peakwanderlust.co.ke", "+254 733 210 406", "Nairobi, Kenya", "Travel Agency"),
    9:  ("Jonas Berg", "Jonas Berg", "jonas.berg@telia.se", "+46 70 445 8832", "Stockholm, Sweden", "Individual"),
    10: ("Sarah Whitfield", "Sarah Whitfield", "s.whitfield@outlook.com", "+44 7700 912233", "London, United Kingdom", "Individual"),
    11: ("Zinduka Foundation", "Anna Mwakalinga", "anna@zindukafoundation.or.tz", "+255 754 611 203", "Arusha, Tanzania", "NGO / Institution"),
    12: ("Mark Johnson", "Mark Johnson", "mjohnson66@gmail.com", "+1 415 290 7788", "San Francisco, USA", "Individual"),
    13: ("Zanzibar Blue Tours", "Fatma Juma", "fatma@zanzibarblue.com", "+255 777 340 918", "Zanzibar, Tanzania", "Tour Operator"),
    14: ("Thandiwe Travel", "Zanele Dlamini", "zanele@thandiwetravel.co.za", "+27 82 771 4490", "Cape Town, South Africa", "Travel Agency"),
    15: ("Emily Raison", "Emily Raison", "emily.raison@gmail.com", "+61 418 552 903", "Melbourne, Australia", "Individual"),
    16: ("Omar Haddad", "Omar Haddad", "omar.haddad@gmail.com", "+971 50 662 7814", "Dubai, UAE", "Individual"),
    18: ("Hippo Safari Agencies", "Robert Ssentongo", "robert@hipposafari.ug", "+256 772 630 471", "Kampala, Uganda", "Travel Agency"),
    19: ("Léa Fontaine", "Léa Fontaine", "lea.fontaine@orange.fr", "+33 6 12 47 88 90", "Lyon, France", "Individual"),
    20: ("Kibo Coffee Co.", "Avash Nyundo", "a.nyundo@kibocoffee.co.tz", "+255 767 220 540", "Arusha, Tanzania", "Corporate"),
    22: ("Bond Safari & Tours", "Daniel Osei", "d.osei@bondsafari.co.uk", "+44 7810 554 092", "Manchester, United Kingdom", "Travel Agency"),
    23: ("Wanjiru Family", "Grace Wanjiru", "g.wanjiru@yahoo.com", "+254 712 908 345", "Nairobi, Kenya", "Individual"),
    24: ("Kili Trails Adventures", "Jacob Lyimo", "jacob@kilitrails.co.tz", "+255 786 145 072", "Moshi, Tanzania", "Travel Agency"),
    25: ("Daniel Schmidt", "Daniel Schmidt", "d.schmidt@freenet.de", "+49 151 772 6094", "Hamburg, Germany", "Individual"),
    27: ("Hippo Safari Agencies", "Robert Ssentongo", "robert@hipposafari.ug", "+256 772 630 471", "Kampala, Uganda", "Travel Agency"),
    28: ("Nadia Kiprop", "Nadia Kiprop", "nadia.kiprop@gmail.com", "+254 721 440 118", "Eldoret, Kenya", "Individual"),
    31: ("Ana López", "Ana López", "ana.lopez@correos.es", "+34 655 211 038", "Barcelona, Spain", "Individual"),
    34: ("Kenji Tanaka", "Kenji Tanaka", "kenji.tanaka@docomo.jp", "+81 90 4412 7883", "Tokyo, Japan", "Individual"),
    36: ("Savannah Tracks Ltd", "Paul Ndungu", "paul@savannahtracks.com", "+254 729 603 771", "Atlanta, USA", "Travel Agency"),
    38: ("David Chen", "David Chen", "david.chen@gmail.com", "+65 8421 8873", "Singapore", "Individual"),
    40: ("Yara Events Ltd", "Salma Rajabu", "s.rajabu@yaraevents.co.tz", "+255 753 990 220", "Arusha, Tanzania", "Corporate"),
    42: ("Marta Nowak", "Marta Nowak", "m.nowak@gmail.com", "+48 601 233 771", "Kraków, Poland", "Individual"),
    43: ("Hanna Virtanen", "Hanna Virtanen", "hanna.virtanen@iki.fi", "+358 40 552 1963", "Helsinki, Finland", "Individual"),
    45: ("Serengeti Gateway Ltd", "Baraka Msuya", "bookings@serengetigateway.co.tz", "+255 784 902 311", "Arusha, Tanzania", "Travel Agency"),
}
# inquiries missing from roster above (dedup repeats handled below)
CLIENTS.setdefault(17, CLIENTS[1])
CLIENTS.setdefault(21, CLIENTS[14])
CLIENTS.setdefault(26, CLIENTS[10])
CLIENTS.setdefault(29, CLIENTS[1])
CLIENTS.setdefault(30, CLIENTS[16])
CLIENTS.setdefault(32, CLIENTS[22])
CLIENTS.setdefault(33, CLIENTS[13])
CLIENTS.setdefault(35, CLIENTS[3])
CLIENTS.setdefault(37, CLIENTS[1])
CLIENTS.setdefault(39, CLIENTS[6])
CLIENTS.setdefault(41, CLIENTS[24])
CLIENTS.setdefault(44, CLIENTS[2])

INQ = [
    (1,  D(2026,8,1),  "Great Migration Safari (5 Days)", 6, 19200, "Email",        "Kelvin Mtui",   "Won",     None, "Repeat agency client; 3rd inquiry this year."),
    (2,  D(2026,8,2),  "Kilimanjaro Trek - Machame Route (7 Days)", 4, 10100, "Referral", "Amina Hassan", "Won", None, "German couple group confirmed at revised price."),
    (3,  D(2026,8,3),  "Ngorongoro & Lake Manyara Day Trip", 2, 1500, "Website", "Joseph Mkumbo", "Open", D(2026,10,30), "Honeymoon; wants private vehicle."),
    (4,  D(2026,8,3),  "Great Migration Safari (5 Days)", 2, 5400, "Website", "Grace Mushi", "Won", None, "Solo couple; river-crossing dates requested."),
    (5,  D(2026,8,5),  "Tarangire Day Trip", 12, 8000, "Referral", "Joseph Mkumbo", "Open", D(2026,11,2), "Corporate team-building day trip."),
    (6,  D(2026,8,8),  "Tarangire & Ngorongoro Safari (4 Days)", 8, 23000, "Trade Fair", "Joseph Mkumbo", "Won", None, "Met at Karibu Travel Fair, August."),
    (7,  D(2026,8,10), "Ngorongoro & Lake Manyara Day Trip", 2, 1200, "Social Media", "Amina Hassan", "Lost", None, "Budget below minimum; declined offer."),
    (8,  D(2026,8,10), "Kilimanjaro Trek - Machame Route (7 Days)", 10, 36000, "Website", "Kelvin Mtui", "Lost", None, "Lost on price - chose local Moshi operator."),
    (9,  D(2026,8,12), "Mount Meru Trek (4 Days)", 2, 3000, "WhatsApp", "Grace Mushi", "Inactive", None, "No reply to 3 contacts; keep in nurture list."),
    (10, D(2026,8,15), "Kilimanjaro Trek - Machame Route (7 Days)", 2, 6000, "Social Media", "Kelvin Mtui", "Open", D(2026,11,9), "For February 2027; deciding route vs Rongai."),
    (11, D(2026,8,18), "Ngorongoro Crater Day Trip", 12, 7200, "Referral", "Amina Hassan", "Won", None, "NGO field trip; invoice to foundation."),
    (12, D(2026,8,19), "Tarangire & Ngorongoro Safari (4 Days)", 4, 9000, "Website", "Grace Mushi", "Open", D(2026,11,3), "Wants to add Zanzibar extension."),
    (13, D(2026,8,20), "Zanzibar Beach Holiday (6 Days)", 10, 25500, "Email", "Joseph Mkumbo", "Won", None, "Honeymoon wedding party group."),
    (14, D(2026,8,22), "Zanzibar Beach Holiday (6 Days)", 8, 26800, "Referral", "Amina Hassan", "Lost", None, "Chose competitor on December dates."),
    (15, D(2026,8,25), "Kilimanjaro Trek - Machame Route (7 Days)", 2, 5400, "WhatsApp", "Kelvin Mtui", "Won", None, "Upgraded to 8-day option."),
    (16, D(2026,8,27), "Great Migration Safari (5 Days)", 6, 20800, "Email", "Amina Hassan", "Open", D(2026,10,28), "Group visas & flights pending; follow-up weekly."),
    (17, D(2026,8,28), "Tarangire & Ngorongoro Safari (4 Days)", 14, 40500, "WhatsApp", "Joseph Mkumbo", "Open", D(2026,10,28), "Youth group; fundraising drive underway."),
    (18, D(2026,8,30), "Great Migration Safari (5 Days)", 7, 22500, "Trade Fair", "Grace Mushi", "Won", None, "Ugandan family; 2 cash payments."),
    (19, D(2026,9,1),  "Zanzibar Beach Holiday (6 Days)", 2, 4300, "Social Media", "Joseph Mkumbo", "Inactive", None, "Budget inquiry only; dormant since Sept."),
    (20, D(2026,9,2),  "Tarangire Day Trip", 16, 10500, "Referral", "Amina Hassan", "Open", D(2026,10,30), "Coffee company end-of-year excursion."),
    (21, D(2026,9,3),  "Tarangire & Ngorongoro Safari (4 Days)", 5, 14000, "Travel Agency", "Amina Hassan", "Won", None, "Repeat agency; group of 5 SA guests."),
    (22, D(2026,9,5),  "Ngorongoro Crater Day Trip", 12, 7900, "Website", "Kelvin Mtui", "Open", D(2026,10,28), "UK corporate party; broad interest."),
    (23, D(2026,9,6),  "Great Migration Safari (5 Days)", 3, 5200, "WhatsApp", "Joseph Mkumbo", "Open", D(2026,11,5), "Family group; has not confirmed dates."),
    (24, D(2026,9,7),  "Mount Meru Trek (4 Days)", 3, 4300, "Referral", "Joseph Mkumbo", "Won", None, "3 friends pre-topping-up Kili climb."),
    (25, D(2026,9,8),  "Ngorongoro Crater Day Trip", 4, 2600, "Website", "Grace Mushi", "Lost", None, "Shared group trip unsuitable; declined."),
    (26, D(2026,9,9),  "Kilimanjaro Trek - Machame Route (7 Days)", 2, 5600, "Email", "Kelvin Mtui", "Won", None, "Repeat client (INQ-010); November climb."),
    (27, D(2026,9,10), "Zanzibar Beach Holiday (6 Days)", 6, 16500, "Email", "Amina Hassan", "Inactive", None, "Late-January plan; dormant since Sept."),
    (28, D(2026,9,11), "Mount Meru Trek (4 Days)", 3, 4600, "Social Media", "Grace Mushi", "Lost", None, "Travel dates moved to 2027 at short notice."),
    (29, D(2026,9,12), "Great Migration Safari (5 Days)", 9, 27600, "WhatsApp", "Grace Mushi", "Won", None, "Repeat agency; migration river camp."),
    (30, D(2026,9,14), "Zanzibar Beach Holiday (6 Days)", 4, 10400, "Website", "Joseph Mkumbo", "Open", D(2026,10,28), "Tentative hold (BK-019); decision imminent."),
    (31, D(2026,9,16), "Ngorongoro Crater Day Trip", 2, 1400, "Website", "Amina Hassan", "Open", D(2026,10,30), "Joined shared picnic group - choosing dates."),
    (32, D(2026,9,17), "Zanzibar Beach Holiday (6 Days)", 8, 21000, "Email", "Amina Hassan", "Won", None, "2nd booking this year for Bond Safari."),
    (33, D(2026,9,18), "Great Migration Safari (5 Days)", 6, 18000, "Referral", "Joseph Mkumbo", "Lost", None, "Partner agency matched cheaper internal rate."),
    (34, D(2026,9,20), "Tarangire & Ngorongoro Safari (4 Days)", 2, 5600, "Website", "Kelvin Mtui", "Open", D(2026,10,30), "Fly-in option requested; awaiting flight tariffs."),
    (35, D(2026,9,22), "Tarangire & Ngorongoro Safari (4 Days)", 4, 11400, "Website", "Joseph Mkumbo", "Won", None, "Early-Christmas family trip in December."),
    (36, D(2026,9,23), "Great Migration Safari (5 Days)", 12, 31500, "Trade Fair", "Amina Hassan", "Lost", None, "US corp travel tendered to DMC in-house."),
    (37, D(2026,9,24), "Kilimanjaro Trek - Machame Route (7 Days)", 8, 22500, "WhatsApp", "Grace Mushi", "Open", D(2026,10,29), "8-person group collecting deposits."),
    (38, D(2026,9,25), "Great Migration Safari (5 Days)", 2, 5500, "Referral", "Kelvin Mtui", "Won", None, "Referral from INQ-026 guests; honeymoon package."),
    (39, D(2026,9,28), "Mount Meru Trek (4 Days)", 6, 8900, "Email", "Joseph Mkumbo", "Open", D(2026,10,31), "Repeat agency; evaluating February window."),
    (40, D(2026,9,29), "Tarangire Day Trip", 15, 10000, "Referral", "Amina Hassan", "Lost", None, "Client event postponed; booking cancelled."),
    (41, D(2026,10,1), "Mount Meru Trek (4 Days)", 4, 5500, "WhatsApp", "Grace Mushi", "Won", None, "Repeat agency; 4 climbing guides needed."),
    (42, D(2026,10,3), "Ngorongoro & Lake Manyara Day Trip", 2, 1500, "Social Media", "Kelvin Mtui", "Open", D(2026,10,28), "Instagram enquiry; replies slow."),
    (43, D(2026,10,6), "Tarangire & Ngorongoro Safari (4 Days)", 3, 6800, "Website", "Grace Mushi", "Open", D(2026,10,28), "Tentative hold (BK-018); awaiting visas."),
    (44, D(2026,10,8), "Kilimanjaro Trek - Machame Route (7 Days)", 6, 15000, "Referral", "Amina Hassan", "Open", D(2026,10,29), "EuropaReisen January 2027 charter group."),
    (45, D(2026,10,9), "Great Migration Safari (5 Days)", 10, 27000, "Website", "Amina Hassan", "Open", D(2026,10,31), "Repeat agency; 10 pax, February."),
]
TRAVEL = {i: t for i, t in zip(
    [1,2,4,6,11,13,15,18,21,24,26,29,30,32,35,38,40,41,43,44],
    [D(2026,8,12),D(2026,8,20),D(2026,9,10),D(2026,9,2),D(2026,9,18),D(2026,9,22),D(2026,10,2),
     D(2026,10,12),D(2026,10,14),D(2026,10,19),D(2026,11,3),D(2026,11,18),D(2027,2,3),D(2026,12,1),
     D(2026,12,5),D(2026,12,15),D(2026,11,8),D(2027,1,8),D(2027,2,25),D(2027,1,15)])}

WON = {1,2,4,6,11,13,15,18,21,24,26,29,32,35,38,41}
LOST = {7,8,14,25,28,33,36,40}
INACTIVE = {9,19,27}
def stage_of(i):
    if i in WON: return "Confirmed", 100
    if i in LOST: return "Lost", 0
    if i in INACTIVE: return "Inactive", 5
    # open: reserves mapping used in the workbook demo
    if i in (16,30,43,44): return "Negotiation", (75 if i == 44 else 60)
    if i == 10: return "Quotation Sent", 45
    if i in (5,17,34,37): return ("Follow-Up", 55) if i == 34 else ("Follow-Up", 50)
    if i in (20,22,39): return "Qualified", 30
    return "New Inquiry", 15
LASTCONTACT = {1:D(2026,8,16),2:D(2026,8,27),3:D(2026,10,24),4:D(2026,9,15),5:D(2026,10,22),6:D(2026,9,6),
    7:D(2026,9,4),8:D(2026,9,2),9:D(2026,9,5),10:D(2026,10,25),11:D(2026,9,19),12:D(2026,10,23),
    13:D(2026,9,25),14:D(2026,9,1),15:D(2026,10,8),16:D(2026,10,20),17:D(2026,10,20),18:D(2026,10,17),
    19:D(2026,9,12),20:D(2026,10,24),21:D(2026,10,15),22:D(2026,10,21),23:D(2026,10,24),24:D(2026,10,21),
    25:D(2026,9,23),26:D(2026,10,7),27:D(2026,9,22),28:D(2026,9,14),29:D(2026,10,12),30:D(2026,10,21),
    31:D(2026,10,23),32:D(2026,10,19),33:D(2026,9,30),34:D(2026,10,21),35:D(2026,10,18),36:D(2026,10,6),
    37:D(2026,10,23),38:D(2026,10,21),39:D(2026,10,20),40:D(2026,10,10),41:D(2026,10,24),42:D(2026,10,25),
    43:D(2026,10,24),44:D(2026,10,24),45:D(2026,10,20)}
NEXTFU = {3:D(2026,10,30),5:D(2026,11,2),10:D(2026,11,9),12:D(2026,11,3),16:D(2026,10,28),17:D(2026,10,28),
    20:D(2026,10,30),22:D(2026,10,28),23:D(2026,11,5),30:D(2026,10,28),31:D(2026,10,30),34:D(2026,10,30),
    37:D(2026,10,29),39:D(2026,10,31),42:D(2026,10,28),43:D(2026,10,28),44:D(2026,10,29),45:D(2026,10,31)}
LOSTREASON = {7:"Budget below minimum; declined offer.",8:"Lost on price - chose local Moshi operator.",
    14:"Chose competitor on December dates.",25:"Shared group trip unsuitable; declined.",
    28:"Travel dates moved to 2027 at short notice.",33:"Partner agency matched cheaper internal rate.",
    36:"US corp travel tendered to DMC in-house.",40:"Client event postponed; booking cancelled."}

BK = [
    ("BK-001", 1,  "Great Migration Safari (5 Days)", D(2026,8,12), D(2026,8,16), 6, "Completed", D(2026,8,3), "Kelvin Mtui", 18400, 18400, 12600, D(2026,8,6), "3x1 deposits; balance due pre-arrival."),
    ("BK-002", 2,  "Kilimanjaro Trek - Machame Route (7 Days)", D(2026,8,20), D(2026,8,26), 4, "Completed", D(2026,8,5), "Amina Hassan", 9800, 9800, 6900, D(2026,8,10), "Direct with guests."),
    ("BK-003", 6,  "Tarangire & Ngorongoro Safari (4 Days)", D(2026,9,2), D(2026,9,5), 8, "Completed", D(2026,8,18), "Joseph Mkumbo", 22400, 22400, 15200, D(2026,8,22), "2 vehicles + pop-up roof."),
    ("BK-004", 4,  "Great Migration Safari (5 Days)", D(2026,9,10), D(2026,9,14), 2, "Completed", D(2026,8,20), "Grace Mushi", 5200, 5200, 3550, D(2026,8,25), "Migration season premium applied."),
    ("BK-005", 11, "Ngorongoro Crater Day Trip", D(2026,9,18), D(2026,9,18), 12, "Completed", D(2026,9,1), "Amina Hassan", 6900, 6900, 4700, D(2026,9,8), "Field-trip invoice via foundation."),
    ("BK-006", 13, "Zanzibar Beach Holiday (6 Days)", D(2026,9,22), D(2026,9,28), 10, "Completed", D(2026,9,2), "Joseph Mkumbo", 24800, 24800, 16800, D(2026,9,10), "Wedding party; seafront resort."),
    ("BK-007", 15, "Kilimanjaro Trek - Machame Route (7 Days)", D(2026,10,2), D(2026,10,8), 2, "Completed", D(2026,9,10), "Kelvin Mtui", 5200, 5200, 3620, D(2026,9,15), "Acclimatisation day in Moshi."),
    ("BK-008", 18, "Great Migration Safari (5 Days)", D(2026,10,12), D(2026,10,16), 7, "Completed", D(2026,9,18), "Grace Mushi", 21800, 21800, 14900, D(2026,9,25), "Family group, mobile money payments."),
    ("BK-009", 21, "Tarangire & Ngorongoro Safari (4 Days)", D(2026,10,14), D(2026,10,17), 5, "Completed", D(2026,9,25), "Amina Hassan", 13500, 13500, 9200, D(2026,10,2), "Repeat agency; 5% rebate applied."),
    ("BK-010", 24, "Mount Meru Trek (4 Days)", D(2026,10,19), D(2026,10,22), 3, "Completed", D(2026,10,5), "Joseph Mkumbo", 4100, 4100, 2800, D(2026,10,12), "Guides booked via Moshi office."),
    ("BK-011", 26, "Kilimanjaro Trek - Machame Route (7 Days)", D(2026,11,3), D(2026,11,9), 2, "Confirmed", D(2026,10,6), "Kelvin Mtui", 5450, 5450, 3700, D(2026,10,20), "Repeat individual client."),
    ("BK-012", 29, "Great Migration Safari (5 Days)", D(2026,11,18), D(2026,11,22), 9, "Confirmed", D(2026,10,9), "Grace Mushi", 27200, 27200, 18400, D(2026,10,25), "Awaiting deposit - chase weekly."),
    ("BK-013", 32, "Zanzibar Beach Holiday (6 Days)", D(2026,12,1), D(2026,12,7), 8, "Confirmed", D(2026,10,14), "Amina Hassan", 20500, 20500, 13900, D(2026,11,5), "Corporate gift-drive extension added."),
    ("BK-014", 35, "Tarangire & Ngorongoro Safari (4 Days)", D(2026,12,5), D(2026,12,8), 4, "Confirmed", D(2026,10,16), "Joseph Mkumbo", 11000, 11000, 7500, D(2026,11,15), "Full prepayment received at booking."),
    ("BK-015", 38, "Great Migration Safari (5 Days)", D(2026,12,15), D(2026,12,19), 2, "Confirmed", D(2026,10,20), "Kelvin Mtui", 5350, 5350, 3650, D(2026,11,20), "Honeymoon; room upgrade negotiated."),
    ("BK-016", 41, "Mount Meru Trek (4 Days)", D(2027,1,8), D(2027,1,11), 4, "Confirmed", D(2026,10,23), "Grace Mushi", 5300, 5300, 3600, D(2026,12,1), "4 guides + porters allocated."),
    ("BK-017", 44, "Kilimanjaro Trek - Machame Route (7 Days)", D(2027,1,15), D(2027,1,21), 6, "In Progress", D(2026,10,22), "Amina Hassan", 14700, None, None, D(2026,12,15), "Awaiting internal flights confirmation."),
    ("BK-018", 43, "Tarangire & Ngorongoro Safari (4 Days)", D(2027,2,25), D(2027,2,28), 3, "Tentative", D(2026,10,12), "Grace Mushi", 6800, None, None, None, "Hold placed 14 days; visas pending."),
    ("BK-019", 30, "Zanzibar Beach Holiday (6 Days)", D(2027,2,3), D(2027,2,9), 4, "Tentative", D(2026,9,28), "Joseph Mkumbo", 10400, None, None, None, "Decision expected before Oct 31."),
    ("BK-020", 40, "Tarangire Day Trip", D(2026,11,8), D(2026,11,8), 15, "Cancelled", D(2026,9,30), "Amina Hassan", 10000, None, None, D(2026,10,25), "Postponed to 2027; deposit refunded."),
]
PM = [
    ("BK-001","WR-INV-1041", D(2026,8,5), "Deposit", 9200, "Bank Transfer", "Kelvin Mtui", "50% group deposit"),
    ("BK-001","WR-INV-1042", D(2026,8,8), "Installment", 6000, "Mobile Money", "Amina Hassan", ""),
    ("BK-002","PAY-3319", D(2026,8,6), "Deposit", 4000, "Card", "Grace Mushi", ""),
    ("BK-002","PAY-3320", D(2026,8,18), "Final Payment", 5800, "Bank Transfer", "Kelvin Mtui", ""),
    ("BK-003","SG-INV-0215", D(2026,8,20), "Deposit", 8000, "Bank Transfer", "Joseph Mkumbo", ""),
    ("BK-003","SG-INV-0216", D(2026,9,1), "Final Payment", 14400, "Bank Transfer", "Amina Hassan", ""),
    ("BK-004","HM-88231", D(2026,8,22), "Deposit", 2000, "Online Gateway", "Grace Mushi", ""),
    ("BK-004","HM-88232", D(2026,9,9), "Final Payment", 3200, "Card", "Kelvin Mtui", ""),
    ("BK-005","ZF-INV-7701", D(2026,9,5), "Final Payment", 6900, "Mobile Money", "Amina Hassan", ""),
    ("BK-006","ZBT-INV-3309", D(2026,9,6), "Deposit", 10000, "Bank Transfer", "Joseph Mkumbo", ""),
    ("BK-006","ZBT-INV-3310", D(2026,9,16), "Final Payment", 14800, "Bank Transfer", "Amina Hassan", ""),
    ("BK-007","ERC-99012", D(2026,9,11), "Deposit", 500, "Card", "Kelvin Mtui", "Booking deposit"),
    ("BK-007","ERC-99013", D(2026,10,1), "Final Payment", 4700, "Online Gateway", "Grace Mushi", ""),
    ("BK-008","HIP-INV-01L", D(2026,9,20), "Deposit", 8000, "Bank Transfer", "Grace Mushi", ""),
    ("BK-008","HIP-INV-01M", D(2026,10,5), "Final Payment", 13800, "Bank Transfer", "Joseph Mkumbo", ""),
    ("BK-009","TT-INV-4410", D(2026,9,27), "Deposit", 5000, "Bank Transfer", "Amina Hassan", ""),
    ("BK-009","TT-INV-4410B", D(2026,10,9), "Final Payment", 8500, "Mobile Money", "Grace Mushi", ""),
    ("BK-010","KT-INV-3390", D(2026,10,10), "Final Payment", 4100, "Mobile Money", "Joseph Mkumbo", "Paid in full at booking"),
    ("BK-011","SW-74120", D(2026,10,9), "Deposit", 2500, "Card", "Kelvin Mtui", ""),
    ("BK-013","BS-INV-1102", D(2026,10,18), "Deposit", 8200, "Online Gateway", "Amina Hassan", "40% deposit"),
    ("BK-014","CR-56201", D(2026,10,20), "Final Payment", 11000, "Bank Transfer", "Joseph Mkumbo", "Full prepayment"),
    ("BK-015","DC-10112", D(2026,10,21), "Deposit", 2000, "Mobile Money", "Kelvin Mtui", ""),
    ("BK-016","KT-INV-3421", D(2026,10,24), "Deposit", 2000, "Bank Transfer", "Grace Mushi", ""),
    ("BK-017","ER-INV-0061", D(2026,10,23), "Deposit", 3000, "Bank Transfer", "Amina Hassan", "Initial charter deposit"),
    ("BK-020","YE-INV-0091", D(2026,10,8), "Deposit", 1200, "Mobile Money", "Amina Hassan", "Event deposit"),
    ("BK-020","YE-RFD-0091", D(2026,10,15), "Refund", -1200, "Bank Transfer", "Amina Hassan", "Deposit refunded after cancellation"),
]
SCH = [
    ('BK-001','Deposit','Group deposit 50%',D(2026,8,5),9200,D(2026,8,5),9200),
    ('BK-001','Installment','Second installment',D(2026,8,8),6000,D(2026,8,8),6000),
    ('BK-001','Final Payment','Balance due before arrival',D(2026,8,10),3200,None,None),
    ('BK-002','Deposit','Booking deposit',D(2026,8,6),4000,D(2026,8,6),4000),
    ('BK-002','Final Payment','Balance due before climb',D(2026,8,10),5800,D(2026,8,18),5800),
    ('BK-003','Deposit','Group deposit',D(2026,8,20),8000,D(2026,8,20),8000),
    ('BK-003','Final Payment','Balance due before arrival',D(2026,8,22),14400,D(2026,9,1),14400),
    ('BK-004','Deposit','Booking deposit',D(2026,8,22),2000,D(2026,8,22),2000),
    ('BK-004','Final Payment','Balance due before arrival',D(2026,8,25),3200,D(2026,9,9),3200),
    ('BK-005','Final Payment','Single full payment (NGO)',D(2026,9,8),6900,D(2026,9,5),6900),
    ('BK-006','Deposit','Group deposit',D(2026,9,6),10000,D(2026,9,6),10000),
    ('BK-006','Final Payment','Balance due before arrival',D(2026,9,10),14800,D(2026,9,16),14800),
    ('BK-007','Deposit','Booking deposit',D(2026,9,11),500,D(2026,9,11),500),
    ('BK-007','Final Payment','Balance (flights + permits)',D(2026,9,15),4700,D(2026,10,1),4700),
    ('BK-008','Deposit','Family group deposit',D(2026,9,20),8000,D(2026,9,20),8000),
    ('BK-008','Final Payment','Balance due before arrival',D(2026,9,25),13800,D(2026,10,5),13800),
    ('BK-009','Deposit','Agency deposit',D(2026,9,27),5000,D(2026,9,27),5000),
    ('BK-009','Final Payment','Balance due before arrival',D(2026,10,2),8500,D(2026,10,9),8500),
    ('BK-010','Final Payment','Single full payment',D(2026,10,12),4100,D(2026,10,10),4100),
    ('BK-011','Deposit','Booking deposit',D(2026,10,9),2500,D(2026,10,9),2500),
    ('BK-011','Final Payment','Balance due before climb',D(2026,10,20),2950,None,None),
    ('BK-012','Final Payment','Balance / full prepayment',D(2026,10,25),27200,None,None),
    ('BK-013','Deposit','Deposit 40%',D(2026,10,18),8200,D(2026,10,18),8200),
    ('BK-013','Final Payment','Balance due before arrival',D(2026,11,5),12300,None,None),
    ('BK-014','Final Payment','Full prepayment at booking',D(2026,11,15),11000,D(2026,10,20),11000),
    ('BK-015','Deposit','Honeymoon deposit',D(2026,10,21),2000,D(2026,10,21),2000),
    ('BK-015','Final Payment','Balance due before arrival',D(2026,11,20),3350,None,None),
    ('BK-016','Deposit','Agency deposit',D(2026,10,24),2000,D(2026,10,24),2000),
    ('BK-016','Final Payment','Balance due before trek',D(2026,12,1),3300,None,None),
    ('BK-017','Deposit','Charter deposit',D(2026,10,23),3000,D(2026,10,23),3000),
    ('BK-017','Final Payment','Balance due before arrival',D(2026,12,15),11700,None,None),
]
FU = [
    (1,"Send booking confirmation & group deposit invoice.",D(2026,8,4),"High","Kelvin Mtui","Completed",D(2026,8,4),"Deposit received; booking confirmed."),
    (2,"Collect final headcount & dietary notes.",D(2026,8,6),"High","Amina Hassan","Completed",D(2026,8,6),""),
    (6,"Prepare travel fair post-visit proposal.",D(2026,8,20),"Medium","Joseph Mkumbo","Completed",D(2026,8,20),"Quote accepted."),
    (4,"Confirm river-crossing itinerary dates.",D(2026,8,25),"Medium","Grace Mushi","Completed",D(2026,8,25),"Dates confirmed."),
    (13,"Final payment schedule to Zanzibar Blue.",D(2026,8,29),"Medium","Joseph Mkumbo","Completed",D(2026,8,29),""),
    (11,"Issue invoice to foundation finance.",D(2026,9,4),"Medium","Amina Hassan","Completed",D(2026,9,4),""),
    (7,"Send revised budget option.",D(2026,9,8),"Low","Amina Hassan","Completed",D(2026,9,8),"Client declined; budget constraint."),
    (15,"Confirm gear list & acclimatisation day.",D(2026,9,14),"High","Kelvin Mtui","Completed",D(2026,9,14),""),
    (21,"Send SA group arrival logistics.",D(2026,9,30),"Medium","Amina Hassan","Completed",D(2026,9,30),""),
    (18,"Verify vehicle allocation for family group.",D(2026,10,1),"Medium","Grace Mushi","Completed",D(2026,10,1),""),
    (24,"Allocate guides & porters for Meru trek.",D(2026,10,8),"High","Joseph Mkumbo","Completed",D(2026,10,8),""),
    (29,"Issue Wild Roots group deposit invoice.",D(2026,10,8),"High","Grace Mushi","Completed",D(2026,10,8),"Deposit invoice sent."),
    (25,"Close out expired quote on shared option.",D(2026,10,9),"Low","Grace Mushi","Completed",D(2026,10,9),"Marked lost this week."),
    (32,"Collect 40% deposit for December stay.",D(2026,10,15),"High","Amina Hassan","Completed",D(2026,10,15),"Deposit received 18 Oct."),
    (16,"Follow up group size decision & flight dates.",D(2026,10,8),"High","Amina Hassan","In Progress",None,"Third reminder sent; response pending."),
    (37,"Chase group deposits for February climb.",D(2026,10,9),"Medium","Grace Mushi","Pending",None,""),
    (34,"Send fly-in tariff comparison.",D(2026,10,12),"Medium","Kelvin Mtui","Pending",None,""),
    (17,"Call coordinator about fundraising result.",D(2026,10,15),"High","Joseph Mkumbo","In Progress",None,""),
    (39,"Share February availability calendar.",D(2026,10,19),"Medium","Joseph Mkumbo","Pending",None,""),
    (42,"Follow up on Lake Manyara date choice.",D(2026,10,21),"Medium","Kelvin Mtui","Pending",None,""),
    (45,"Send February migration pricing sheet.",D(2026,10,22),"High","Amina Hassan","Pending",None,""),
    (22,"Prepare January departure quotation.",D(2026,10,28),"High","Kelvin Mtui","Pending",None,""),
    (31,"Confirm shared-group join dates.",D(2026,10,30),"Medium","Amina Hassan","Pending",None,""),
    (12,"Quote Zanzibar extension option.",D(2026,11,3),"Medium","Grace Mushi","Pending",None,""),
    (23,"Second call to agree travel window.",D(2026,11,5),"Low","Joseph Mkumbo","Pending",None,""),
    (10,"Follow up on February proposal.",D(2026,11,9),"Medium","Kelvin Mtui","Pending",None,""),
    (40,"Cancellation admin & refund filing.",D(2026,10,10),"Medium","Amina Hassan","Cancelled",None,"Event postponed; deposit refunded."),
]
QT_ACCEPTED = {1:"BK-001",2:"BK-002",4:"BK-004",6:"BK-003",11:"BK-005",13:"BK-006",15:"BK-007",18:"BK-008",21:"BK-009",24:"BK-010",26:"BK-011",29:"BK-012",32:"BK-013",35:"BK-014",38:"BK-015",41:"BK-016"}
QT_ACCEPT_DATES = {1:D(2026,8,2),2:D(2026,8,3),4:D(2026,8,19),6:D(2026,8,15),11:D(2026,8,28),13:D(2026,8,27),
    15:D(2026,9,6),18:D(2026,9,14),21:D(2026,9,23),24:D(2026,10,2),26:D(2026,10,3),29:D(2026,10,5),
    32:D(2026,10,10),35:D(2026,10,12),38:D(2026,10,16),41:D(2026,10,20)}
QT_SENT = {30:10200,43:6600,10:6000,16:20800,37:22200}
QT_REVISED = {44:14400}
QT_DRAFT = {22:7700}
QT_REJECTED = {8:34500,14:26000,33:17300,36:30500}
QT_EXPIRED = {7:1150,25:2500,28:4400}
TGT = [(8,"Kelvin Mtui",18000),(8,"Amina Hassan",12000),(8,"Joseph Mkumbo",21000),(8,"Grace Mushi",6500),
       (9,"Kelvin Mtui",6000),(9,"Amina Hassan",19000),(9,"Joseph Mkumbo",26500),(9,"Grace Mushi",20000),
       (10,"Kelvin Mtui",12000),(10,"Amina Hassan",24000),(10,"Joseph Mkumbo",16000),(10,"Grace Mushi",34000)]
MONTHNOTE = {8:"High season ramp-up.",9:"Peak migration demand.",10:"Strong Zanzibar + Kilimanjaro pipeline."}
ACT = [
    (D(2026,8,2),1,"Email","Sent migration itinerary + prices.","Await reply",D(2026,8,4),"Kelvin Mtui",25),
    (D(2026,8,5),2,"WhatsApp","Shared Machame route guide & packing list.","Send final headcount form",D(2026,8,8),"Amina Hassan",20),
    (D(2026,8,8),4,"Call","Discussed river-crossing camp preferences.","Email confirmation letter",D(2026,8,10),"Grace Mushi",18),
    (D(2026,8,12),6,"Meeting","Karibu Travel Fair stand meeting with agency.","Send trading proposal",D(2026,8,16),"Joseph Mkumbo",45),
    (D(2026,8,15),10,"Email","Sent route comparison offer (Machame vs Rongai).","Second email in a week",D(2026,9,1),"Kelvin Mtui",30),
    (D(2026,8,17),11,"WhatsApp","Confirmed field-trip date with foundation.","Issue invoice",D(2026,9,1),"Amina Hassan",15),
    (D(2026,8,21),13,"Call","Reviewed resort upgrade options for Zanzibar.","Book seafront block",D(2026,8,24),"Joseph Mkumbo",35),
    (D(2026,8,28),17,"WhatsApp","Discussed youth-group fundraising timeline.","Send batch quote incl. own kit",D(2026,9,5),"Joseph Mkumbo",22),
    (D(2026,9,2),20,"Email","Coffee company year-end excursion options.","Schedule site visit",D(2026,9,12),"Amina Hassan",30),
    (D(2026,9,5),22,"Email","Sent Ngorongoro day-trip options to UK group.","Await shortlist",D(2026,9,18),"Kelvin Mtui",20),
    (D(2026,9,11),26,"Call","Repeat client - moved climb to November.","Send booking contract",D(2026,9,16),"Kelvin Mtui",25),
    (D(2026,9,14),30,"Meeting","Omar tentative hold; deposit deadline set.","Chase decision",D(2026,10,20),"Joseph Mkumbo",40),
    (D(2026,9,17),32,"Email","Bond Safari December proposal finalised.","Collect 40% deposit",D(2026,10,10),"Amina Hassan",30),
    (D(2026,9,19),35,"Call","Walked family through December itinerary.","Send confirmable dates",D(2026,10,1),"Joseph Mkumbo",28),
    (D(2026,9,23),38,"WhatsApp","Honeymoon - shared migration lodge options.","Confirm balloon ride addition",D(2026,10,3),"Kelvin Mtui",20),
    (D(2026,9,26),41,"Call","Kili Trails - allocated guides for Meru.","Send crew cost sheet",D(2026,10,5),"Grace Mushi",18),
    (D(2026,9,30),43,"Meeting","Virtual meeting - itinerary walkthrough.","Hold pax & rooms for 14 days",D(2026,10,12),"Grace Mushi",45),
    (D(2026,10,3),44,"Email","EuropaReisen January charter - cost sheet shared.","Revise if flights change",D(2026,10,16),"Amina Hassan",35),
    (D(2026,10,6),45,"Email","February migration quote themes for 10 pax.","Send detailed pricing",D(2026,10,13),"Amina Hassan",26),
    (D(2026,10,9),42,"WhatsApp","Marta - Lake Manyara day trip follow-up.","Confirm date in October",D(2026,10,28),"Kelvin Mtui",12),
    (D(2026,10,13),31,"Call","Ana - shared picnic group join options.","Send joinable dates list",D(2026,10,30),"Amina Hassan",15),
    (D(2026,10,16),37,"Call","Wild Roots - deposits chase for group of 8.","Escalate if unpaid by 25 Oct",D(2026,10,26),"Grace Mushi",22),
    (D(2026,10,20),34,"Email","Kenji - fly-in tariff comparison document.","Follow up in 3 days",D(2026,10,30),"Kelvin Mtui",30),
    (D(2026,10,24),22,"Email","Bond Safari - gift-drive extension idea.","Draft January quote",D(2026,10,28),"Kelvin Mtui",25),
]
CLIENT_NOTES = {1:"Repeat agency; multiple inquiries YTD.",2:"Charter group planned for January 2027.",
    8:"Lost on price - revisit with promo rates.",10:"Repeat client; February climb in pipeline.",
    13:"Strong repeat potential.",14:"December load lost; Oct trip completed.",
    16:"Tentative Zanzibar hold; weekly updates running.",20:"Different entity from Arusha Coffee Collective.",
    22:"2 inquiries YTD; 1 confirmed.",24:"Both inquiries converted.",27:"Event postponed; deposit refunded.",
    29:"US tender lost to in-house DMC.",31:"Tentative hold for February.",40:"Postponed to 2027; deposit refunded."}

LISTS = {
    'lead_source': ["Website","Email","Phone","WhatsApp","Social Media","Travel Agency","Referral","Trade Fair","Repeat Client","Other"],
    'sales_stage': ["New Inquiry","Qualified","Quotation Sent","Follow-Up","Negotiation","Confirmed","Lost","Inactive"],
    'lead_status': ["Open","Won","Lost","Inactive"],
    'quotation_status': ["Draft","Sent","Revised","Accepted","Rejected","Expired"],
    'booking_status': ["Tentative","Confirmed","In Progress","Completed","Cancelled"],
    'task_status': ["Pending","In Progress","Completed","Cancelled"],
    'priority': ["High","Medium","Low"],
    'activity_type': ["Call","Email","WhatsApp","Meeting","Quotation Sent","Follow-Up","Other"],
    'client_type': ["Individual","Travel Agency","Corporate","Tour Operator","NGO / Institution","Other"],
    'payment_type': ["Deposit","Installment","Final Payment","Refund"],
    'payment_method': ["Bank Transfer","Cash","Mobile Money","Card","Online Gateway","Other"],
    'currency': ["TZS","USD","EUR","GBP","KES"],
}
FX = {"USD":1.0,"TZS":0.000385,"EUR":1.09,"GBP":1.27,"KES":0.0077}

def q(v):
    if v is None: return "NULL"
    if isinstance(v, (int, float)): return str(v)
    if isinstance(v, D): return "'" + v.isoformat() + "'"
    return "'" + str(v).replace("\\", "\\\\").replace("'", "\\'") + "'"

out = []
out.append("-- ============================================================")
out.append("--  demo seed data (Aug-Oct 2026) - generated from the Excel demo")
out.append("--  Import AFTER schema.sql into the DB named in .envWebVersion.")
out.append("-- ============================================================")
out.append("SET FOREIGN_KEY_CHECKS = 0;")
out.append("TRUNCATE users; TRUNCATE list_items; TRUNCATE fx_rates; TRUNCATE settings;")
out.append("TRUNCATE clients; TRUNCATE inquiries; TRUNCATE opportunities; TRUNCATE quotations;")
out.append("TRUNCATE follow_ups; TRUNCATE bookings; TRUNCATE payment_schedule; TRUNCATE payments;")
out.append("TRUNCATE sales_targets; TRUNCATE activities;")
out.append("TRUNCATE quote_days; TRUNCATE quote_items; TRUNCATE quote_views;")
out.append("TRUNCATE travelers; TRUNCATE content_library; TRUNCATE message_templates; TRUNCATE task_templates;")
out.append("")

# users (1=admin, 2..5 team)
out.append("-- users - password for everyone: demo1234 (change after first login!)")
uid = {}
for i, name in enumerate(["System Admin"] + TEAM, 1):
    email = "admin@michaeltourism.com" if i == 1 else EMAILS[name]
    role = "admin" if i == 1 else ("manager" if name == "Kelvin Mtui" else "sales")
    salt, h = phash(DEMO_PASSWORD)
    uid[name] = i
    out.append(f"INSERT INTO users (id,name,email,salt,password_hash,role,active) VALUES ({i},{q(name)},{q(email)},{q(salt)},{q(h)},{q(role)},1);")
people = {name: i for i, name in enumerate(TEAM, 2)}
out.append("")

# lists + fx
out.append("-- dropdown lists")
for lname, values in LISTS.items():
    vals = ", ".join(f"({q(lname)},{q(v)},{s})" for s, v in enumerate(values, 1))
    out.append(f"INSERT INTO list_items (list_name,value,sort) VALUES {vals};")
out.append("")
out.append("-- fx rates to base currency (editable in Settings)")
fx_rows = ", ".join(f"({q(k)},{v})" for k, v in FX.items())
out.append(f"INSERT INTO fx_rates (currency,rate_to_base) VALUES {fx_rows};")
out.append("INSERT INTO settings (k,v) VALUES ('company_name','Michael Tourism'),('base_currency','USD');")
out.append("")

# clients: dedupe by name, map inquiry idx -> client id
out.append("-- clients")
client_id_of = {}
crows = []
cid = 0
for row in INQ:
    idx = row[0]
    name = CLIENTS[idx][0]
    if name in client_id_of:
        client_id_of[idx] = client_id_of[name]
        continue
    cid += 1
    client_id_of[idx] = cid
    client_id_of[name] = cid
    _, contact, email, phone, country, ctype = CLIENTS[idx]
    owner = people[row[6]]
    note = CLIENT_NOTES.get(row[0], "")
    crows.append(f"({cid},{q(name)},{q(ctype)},{q(country)},{q(contact)},{q(email)},{q(phone)},{q(row[1])},{owner},{q(note)})")
out.append("INSERT INTO clients (id,name,client_type,country,contact_person,email,phone,first_inquiry,owner_id,notes) VALUES")
out.append(",\n".join(crows) + ";")
out.append("")

# inquiries (id = inquiry idx, so code INQ-001 matches the workbook)
out.append("-- inquiries (id matches the Excel demo INQ-xxx codes)")
irows = []
for row in INQ:
    idx, drecv, tour, pax, budget, source, person, status, nextfu, note = row
    name, contact, email, phone, country, ctype = CLIENTS[idx]
    irows.append(f"({idx},{q(drecv)},{client_id_of[idx]},{q(contact)},{q(email)},{q(phone)},{q(country)},{q(ctype)},{q(source)},{q(tour)},{q(TRAVEL.get(idx))},{pax},{budget},'USD',{people[person]},{q(status)},{q(nextfu)},{q(note)})")
out.append("INSERT INTO inquiries (id,received_date,client_id,contact_person,email,phone,country,client_type,lead_source,tour,travel_date,pax,budget,currency,assigned_to,lead_status,next_follow_up,notes) VALUES")
out.append(",\n".join(irows) + ";")
out.append("")

# opportunities (id = inquiry idx, code OPP-001)
out.append("-- opportunities (weighted value computed in app)")
inq_first_bk = {}
for i, b in enumerate(BK, 1):
    inq_first_bk.setdefault(b[1], b[9])
orows = []
for row in INQ:
    idx = row[0]
    stage, prob = stage_of(idx)
    est = inq_first_bk.get(idx) if idx in inq_first_bk else int(row[4]*0.95)//50*50
    exp_close = D(2026,10,28) if idx in (30,43) else D(2027,1,31)
    orows.append(f"({idx},{idx},{q(stage)},{q(row[1])},{q(exp_close)},{est},'USD',{prob},{people[row[6]]},{q(LASTCONTACT[idx])},{q(NEXTFU.get(idx))},{q(row[7])},{q(LOSTREASON.get(idx,''))})")
out.append("INSERT INTO opportunities (id,inquiry_id,sales_stage,opened_date,expected_close,estimated_value,currency,probability,assigned_to,last_contact,next_follow_up,lead_status,lost_reason) VALUES")
out.append(",\n".join(orows) + ";")
out.append("")

# quotations (30 rows, ids sequential -> QT-001..)
out.append("-- quotations")
qrows = []
qn = 0
BKDB = {b[0]: i for i, b in enumerate(BK, 1)}
def qtrow(idx, amt, status, qdate, booking=None, version=1, note=""):
    global qn
    qn += 1
    bid = BKDB[booking] if booking else None
    qrows.append(f"({qn},{idx},{bid if booking else 'NULL'},{q(qdate)},{q(qdate+dt.timedelta(days=21))},{q(INQ[idx-1][2])},{INQ[idx-1][3]},{amt},'USD',{q(status)},{people[INQ[idx-1][6]]},{version},{q(note)})")
for idx in QT_ACCEPTED:
    qtrow(idx, inq_first_bk[idx], "Accepted", QT_ACCEPT_DATES[idx], QT_ACCEPTED[idx], 2, "Accepted; converted to booking.")
for idx, amt in QT_SENT.items():
    qtrow(idx, amt, "Sent", INQ[idx-1][1]+dt.timedelta(days=6), None, 1, "Awaiting client decision.")
for idx, amt in QT_REVISED.items():
    qtrow(idx, amt, "Revised", D(2026,10,20), None, 2, "Revised route & pricing 16 Oct.")
for idx, amt in QT_DRAFT.items():
    qtrow(idx, amt, "Draft", D(2026,10,22), None, 1, "Draft pending internal cost check.")
for idx, amt in QT_REJECTED.items():
    qtrow(idx, amt, "Rejected", INQ[idx-1][1]+dt.timedelta(days=10), None, 1, "Rejected on price.")
for idx, amt in QT_EXPIRED.items():
    qtrow(idx, amt, "Expired", INQ[idx-1][1]+dt.timedelta(days=8), None, 1, "Quote lapsed without response.")
out.append("INSERT INTO quotations (id,inquiry_id,booking_id,quote_date,valid_until,tour,pax,amount,currency,status,prepared_by,version,notes) VALUES")
out.append(",\n".join(qrows) + ";")
out.append("")

# bookings (id 1..20 matching BK-xxx)
out.append("-- bookings (id matches the Excel demo BK-xxx codes)")
brows = []
for i, b in enumerate(BK, 1):
    _, inq, tour, arr, dep, pax, status, bdate, person, K, M, N, due, note = b
    brows.append(f"({i},{inq},{client_id_of[inq]},{q(tour)},{q(arr)},{q(dep)},{pax},{q(status)},{q(bdate)},{people[person]},{K},'USD',{q(M)},{q(N)},{q(due)},{q(note)})")
out.append("INSERT INTO bookings (id,inquiry_id,client_id,tour,arrival,departure,pax,status,booking_date,salesperson,booking_value,currency,actual_revenue,direct_cost,payment_due_date,notes) VALUES")
out.append(",\n".join(brows) + ";")
out.append("")

# payment schedule (ids sequential -> SCH-001..)
out.append("-- payment schedule")
srows = []
for i, s in enumerate(SCH, 1):
    bk, ptype, desc, due, adue, dpaid, apaid = s
    srows.append(f"({i},{BKDB[bk]},{q(ptype)},{q(desc)},{q(due)},{adue},{q(dpaid)},{q(apaid)})")
out.append("INSERT INTO payment_schedule (id,booking_id,payment_type,description,due_date,amount_due,date_paid,amount_paid) VALUES")
out.append(",\n".join(srows) + ";")
out.append("")

# payments (ids sequential -> PAY-001..)
out.append("-- payments")
prows = []
for i, p in enumerate(PM, 1):
    bk, ref, d, ptype, amt, meth, by, note = p
    prows.append(f"({i},{BKDB[bk]},{q(ref)},{q(d)},{q(ptype)},{amt},'USD',{q(meth)},{people[by]},{q(note)})")
out.append("INSERT INTO payments (id,booking_id,invoice_ref,payment_date,payment_type,amount,currency,method,received_by,notes) VALUES")
out.append(",\n".join(prows) + ";")
out.append("")

# follow-ups (FU-001..)
out.append("-- follow-ups")
frows = []
for i, f in enumerate(FU, 1):
    inq, action, due, prio, person, status, done, note = f
    frows.append(f"({i},{inq},{q(action)},{q(due)},{q(prio)},{people[person]},{q(status)},{q(done)},{q(note)})")
out.append("INSERT INTO follow_ups (id,inquiry_id,action,due_date,priority,assigned_to,status,completed_date,notes) VALUES")
out.append(",\n".join(frows) + ";")
out.append("")

# targets
out.append("-- sales targets")
torows = []
for i, (m, person, target) in enumerate(TGT, 1):
    torows.append(f"({i},{m},2026,{people[person]},{target},{q(MONTHNOTE[m])})")
out.append("INSERT INTO sales_targets (id,month,year,salesperson,target_revenue,notes) VALUES")
out.append(",\n".join(torows) + ";")
out.append("")

# activities
out.append("-- activities")
arows = []
for i, a in enumerate(ACT, 1):
    d_, inq, atype, summary, naction, ndate, person, mins = a
    arows.append(f"({i},{q(d_)},{inq},{q(atype)},{q(summary)},{q(naction)},{q(ndate)},{people[person]},{mins})")
out.append("INSERT INTO activities (id,activity_date,inquiry_id,activity_type,summary,next_action,next_action_date,salesperson,duration_min) VALUES")
out.append(",\n".join(arows) + ";")
out.append("")
out.append("SET FOREIGN_KEY_CHECKS = 1;")

path = os.path.join(os.path.dirname(__file__), 'seed.sql')
with open(path, 'w', encoding='utf-8') as f:
    f.write("\n".join(out) + "\n")
print(f"seed.sql written: {len(out)} lines")
