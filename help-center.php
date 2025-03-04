<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
?>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <div class="col-md-10 main-content">
        <div class="mb-4 fade-in">
            <h3 class="fw-bold text-primary"><i class="fas fa-question-circle me-2"></i> Qendra e Ndihmës</h3>
            <p class="text-muted">Gjeni përgjigje për pyetjet e shpeshta dhe informacion shtesë rreth platformës sonë.</p>
        </div>

        <div class="accordion slide-up" id="helpAccordion">

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <i class="fas fa-question me-2 text-info"></i> Si të përditësoj fjalëkalimin tim?
                        </button>
                    </h2>
                </div>
                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Për të përditësuar fjalëkalimin tuaj, ndiqni këto hapa:</p>
                        <ol>
                            <li>Shkoni tek menuja e cilësimeve duke klikuar ikonën e profilit tuaj në pjesën anësore.</li>
                            <li>Klikoni në opsionin "Cilësimet e Llogarisë".</li>
                            <li>Në seksionin e fjalëkalimit, fusni fjalëkalimin e ri. Nëse dëshironi të ruani fjalëkalimin aktual, lini fushën e fjalëkalimit bosh.</li>
                            <li>Klikoni butonin "Përditëso Llogarinë" për të ruajtur ndryshimet.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingTwo">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <i class="fas fa-question me-2 text-info"></i> Çfarë janë Raportet dhe si t'i përdor?
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Seksioni i Raporteve ju ofron një pasqyrë të detajuar të të dhënave tuaja. Këtu mund të gjeneroni raporte të ndryshme, si:</p>
                        <ul>
                            <li>Raportet e shitjeve për periudha të caktuara kohore.</li>
                            <li>Raportet e performancës së produktit.</li>
                            <li>Raportet e klientëve dhe të tjera.</li>
                        </ul>
                        <p>Për të përdorur raportet, shkoni në menunë "Raportet" në pjesën anësore dhe zgjidhni tipin e raportit që dëshironi të gjeneroni. Përcaktoni periudhën kohore dhe klikoni "Gjenero Raportin".</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingThree">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            <i class="fas fa-question me-2 text-info"></i> Kalendari dhe funksionet e tij?
                        </button>
                    </h2>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Kalendari është një mjet i fuqishëm për të menaxhuar dhe planifikuar aktivitetet tuaja. Me kalendarin mund të:</p>
                        <ul>
                            <li>Shikoni ngjarjet dhe afatet e ardhshme.</li>
                            <li>Planifikoni takime dhe detyra të reja.</li>
                            <li>Vendosni kujtues për ngjarjet e rëndësishme.</li>
                        </ul>
                        <p>Për të hyrë në kalendar, klikoni në menunë "Kalendari" në pjesën anësore. Për të shtuar një ngjarje të re, klikoni në datën përkatëse në kalendar dhe plotësoni detajet e ngjarjes.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingFour">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                            <i class="fas fa-question me-2 text-info"></i> Çfarë është Paneli i Kontrollit dhe si ta përdor atë?
                        </button>
                    </h2>
                </div>
                <div id="collapseFour" class="collapse" aria-labelledby="headingFour" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Paneli i Kontrollit është ekrani juaj qendror përmbledhës. Ai ofron një pamje të shpejtë të informacioneve kryesore, si:</p>
                        <ul>
                            <li>**Treguesit Kryesorë të Performancës (KPI):** Metrika të rëndësishme të shfaqura vizualisht (p.sh., totali i faturave, detyrat në pritje, raportet e fundit).</li>
                            <li>**Veprimet e Shpejta:** Shkurtore për veçoritë ose detyrat e përdorura shpesh.</li>
                            <li>**Aktiviteti i Fundit:** Një regjistër i ngjarjeve ose përditësimeve të fundit në sistem.</li>
                        </ul>
                        <p>Për të përdorur panelin e kontrollit, thjesht lundroni te "Paneli i Kontrollit" në menunë anësore. Informacioni i shfaqur përditësohet në mënyrë dinamike në kohë reale.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingFive">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                            <i class="fas fa-question me-2 text-info"></i> Si të krijoj një Faturë të re?
                        </button>
                    </h2>
                </div>
                <div id="collapseFive" class="collapse" aria-labelledby="headingFive" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Për të krijuar një faturë të re, ju lutemi ndiqni këto hapa:</p>
                        <ol>
                            <li>Lundroni në seksionin "Faturat" në menunë anësore.</li>
                            <li>Klikoni butonin "Krijo Faturë të Re" (zakonisht ndodhet në pjesën e sipërme djathtas ose poshtë të faqes).</li>
                            <li>Plotësoni detajet e faturës në formularin e ofruar:
                                <ul>
                                    <li>**Informacionet e Klientit:** Zgjidhni një klient ekzistues ose shtoni një të ri.</li>
                                    <li>**Artikujt e Faturës:** Shtoni artikuj linjë për çdo shërbim ose produkt, duke përfshirë përshkrimin, sasinë dhe çmimin.</li>
                                    <li>**Numri dhe Data e Faturës:** Këto mund të gjenerohen automatikisht ose mund t'ju duhet t'i fusni ato.</li>
                                    <li>**Taksa dhe Zbritjet:** Aplikoni çdo taksë ose zbritje të zbatueshme.</li>
                                    <li>**Shënime:** Shtoni ndonjë shënim specifik ose kushte pagese.</li>
                                </ul>
                            </li>
                            <li>Rishikoni të gjitha detajet dhe klikoni "Ruaj Faturën" ose "Krijo Faturën" për të finalizuar dhe gjeneruar faturën.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingSix">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                            <i class="fas fa-question me-2 text-info"></i> Si mund t'i shikoj Raportet e mia të kaluara?
                        </button>
                    </h2>
                </div>
                <div id="collapseSix" class="collapse" aria-labelledby="headingSix" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Për të hyrë në raportet tuaja të kaluara, ndiqni këto hapa:</p>
                        <ol>
                            <li>Shkoni në seksionin "Raportet" nga menyja anësore.</li>
                            <li>Zakonisht do të shihni një listë ose një histori të raporteve të gjeneruara.</li>
                            <li>Klikoni mbi titullin e raportit ose butonin "Shiko" për të hapur dhe rishikuar një raport specifik.</li>
                            <li>Mund të keni opsione për të filtruar raportet sipas datës, llojit ose kritereve të tjera.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingSeven">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                            <i class="fas fa-question me-2 text-info"></i> Cilat janë kërkesat e sistemit për të përdorur këtë aplikacion?
                        </button>
                    </h2>
                </div>
                <div id="collapseSeven" class="collapse" aria-labelledby="headingSeven" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Ky aplikacion ueb është dizajnuar për të funksionuar pa probleme në shfletuesit modernë të uebit. Kërkesat e rekomanduara të sistemit përfshijnë:</p>
                        <ul>
                            <li>**Shfletuesi:** Versionet më të fundit të Chrome, Firefox, Safari ose Edge.</li>
                            <li>**Sistemi Operativ:** Windows, macOS, Linux, Android, iOS.</li>
                            <li>**Lidhja e Internetit:** Lidhje interneti e qëndrueshme për performancë optimale.</li>
                            <li>**Rezolucioni i Ekranit:** Rezolucioni minimal 1024x768 rekomandohet për përvojë shikimi më të mirë.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingEight">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingEight" aria-expanded="false" aria-controls="headingEight">
                            <i class="fas fa-question me-2 text-info"></i> Si të kontaktoj mbështetjen nëse kam nevojë për më shumë ndihmë?
                        </button>
                    </h2>
                </div>
                <div id="collapseEight" class="collapse" aria-labelledby="headingEight" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Nëse nuk mund të gjeni përgjigjen për pyetjen tuaj në Qendrën e Ndihmës, mund të kontaktoni ekipin tonë të mbështetjes përmes kanaleve të mëposhtme:</p>
                        <ul>
                            <li>**Email:** Na dërgoni një email në <a href="mailto:support@example.com">support@example.com</a>.</li>
                            <li>**Help Desk:** Dorëzoni një biletë përmes portalit tonë online <a href="#">Help Desk</a>.</li>
                            <li>**Telefon:** Na telefononi në +1-555-123-4567 gjatë orarit të punës.</li>
                        </ul>
                        <p>Ne synojmë t'u përgjigjemi të gjitha kërkesave brenda 24 orëve të punës.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingNine">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingNine" aria-expanded="false" aria-controls="headingNine">
                            <i class="fas fa-question me-2 text-info"></i> A mund ta personalizoj Panelin e Kontrollit?
                        </button>
                    </h2>
                </div>
                <div id="headingNine" class="collapse" aria-labelledby="headingNine" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Po, në shumicën e rasteve, Paneli i Kontrollit është i personalizueshëm. Kërkoni për një buton "Personalizo Panelin e Kontrollit" ose "Edito Widget-et", zakonisht i vendosur në këndin e sipërm djathtas të faqes së Panelit të Kontrollit. Klikoni mbi të për të:</p>
                        <ul>
                            <li>Shtuar, hequr ose riorganizuar widget-et.</li>
                            <li>Zgjedhur se cilat të dhëna të shfaqen në secilin widget.</li>
                            <li>Rregulluar pamjen dhe paraqitjen e Panelit tuaj të Kontrollit.</li>
                        </ul>
                        <p>Ndryshimet që bëni ruhen zakonisht dhe do të reflektohen sa herë që të logoheni.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingTen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingTen" aria-expanded="false" aria-controls="headingTen">
                            <i class="fas fa-question me-2 text-info"></i> Si të menaxhoj profilet e përdoruesve?
                        </button>
                    </h2>
                </div>
                <div id="headingTen" class="collapse" aria-labelledby="headingTen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Menaxhimi i profileve të përdoruesve varet nga roli dhe lejet tuaja të përdoruesit. Në përgjithësi, ju mund të menaxhoni profilin tuaj duke:</p>
                        <ol>
                            <li>Klikuar ikonën e profilit tuaj ose emrin në pjesën anësore ose kokë.</li>
                            <li>Zgjedhur "Profili" ose "Llogaria Ime".</li>
                            <li>Që aty, zakonisht mund të editoni informacionin personal, të ndryshoni fjalëkalimin (siç përshkruhet në një FAQ tjetër) dhe të menaxhoni cilësimet e njoftimeve.</li>
                        </ol>
                        <p>Administratorët zakonisht kanë aftësi më të gjera menaxhimi të përdoruesve, duke i lejuar ata të krijojnë, editojnë dhe çaktivizojnë llogaritë e përdoruesve.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingEleven">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingEleven" aria-expanded="false" aria-controls="headingEleven">
                            <i class="fas fa-question me-2 text-info"></i> Çfarë lloj Raportesh mund të gjeneroj?
                        </button>
                    </h2>
                </div>
                <div id="headingEleven" class="collapse" aria-labelledby="headingEleven" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Sistemi mbështet një shumëllojshmëri tipash raportesh për t'ju ndihmuar të analizoni të dhënat tuaja në mënyrë efektive. Disa lloje të zakonshme raportesh përfshijnë:</p>
                        <ul>
                            <li>**Raportet e Shitjeve:** Ndiqni performancën e shitjeve me kalimin e kohës, sipas produktit, sipas klientit, etj.</li>
                            <li>**Raportet Financiare:** Gjeneroni përmbledhje të të ardhurave, shpenzimeve dhe fitimit.</li>
                            <li>**Raportet e Aktivitetit:** Monitoroni aktivitetin e përdoruesve, përdorimin e sistemit dhe gjurmët e auditimit.</li>
                            <li>**Raportet e Inventarit:** Menaxhoni nivelet e stokut dhe ndiqni lëvizjet e inventarit.</li>
                            <li>**Raportet e Klientëve:** Analizoni sjelljen dhe demografinë e klientëve.</li>
                        </ul>
                        <p>Tipet e raporteve të disponueshme mund të varen nga niveli juaj i abonimit dhe konfigurimi i sistemit. Kontrolloni menunë "Raportet" për një listë të plotë opsionesh.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingTwelve">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingTwelve" aria-expanded="false" aria-controls="headingTwelve">
                            <i class="fas fa-question me-2 text-info"></i> Si të vendos autentifikimin me dy faktorë?
                        </button>
                    </h2>
                </div>
                <div id="headingTwelve" class="collapse" aria-labelledby="headingTwelve" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Për siguri të shtuar, ne rekomandojmë vendosjen e autentifikimit me dy faktorë (2FA). Për të aktivizuar 2FA:</p>
                        <ol>
                            <li>Shkoni te "Cilësimet e Llogarisë" ose "Cilësimet e Profilit".</li>
                            <li>Kërkoni për një opsion "Siguria" ose "Autentifikimi me Dy Faktorë".</li>
                            <li>Ndiqni udhëzimet për të vendosur 2FA, që zakonisht përfshin skanimin e një kodi QR me një aplikacion autentifikues (si Google Authenticator ose Authy) në smartphone-in tuaj.</li>
                            <li>Ruani kodet tuaja të rikuperimit në një vend të sigurt në rast se humbni aksesin në aplikacionin tuaj autentifikues.</li>
                        </ol>
                        <p>Pasi të aktivizohet, do t'ju duhet të fusni një kod nga aplikacioni juaj autentifikues sa herë që logoheni nga një pajisje e re.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingThirteen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingThirteen" aria-expanded="false" aria-controls="headingThirteen">
                            <i class="fas fa-question me-2 text-info"></i> Çfarë të bëj nëse harroj emrin tim të përdoruesit?
                        </button>
                    </h2>
                </div>
                <div id="headingThirteen" class="collapse" aria-labelledby="headingThirteen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Nëse keni harruar emrin tuaj të përdoruesit, ju lutemi kontaktoni administratorin e sistemit tuaj ose ekipin e mbështetjes. Emrat e përdoruesve janë zakonisht unikë dhe nuk mund të rikuperohen automatikisht për arsye sigurie.</p>
                        <p>Jepni atyre adresën tuaj të emailit të regjistruar ose informacion tjetër identifikues, dhe ata do t'ju ndihmojnë në rikuperimin ose rivendosjen e emrit tuaj të përdoruesit.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingFourteen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingFourteen" aria-expanded="false" aria-controls="headingFourteen">
                            <i class="fas fa-question me-2 text-info"></i> Si mund të eksportoj të dhëna nga sistemi?
                        </button>
                    </h2>
                </div>
                <div id="headingFourteen" class="collapse" aria-labelledby="headingFourteen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Funksionaliteti i eksportimit të të dhënave është i disponueshëm në seksione të ndryshme të aplikacionit, veçanërisht brenda raporteve dhe listave të të dhënave. Për të eksportuar të dhëna:</p>
                        <ol>
                            <li>Lundroni në seksionin që përmban të dhënat që dëshironi të eksportoni (p.sh., "Faturat", "Raportet", "Lista e Klientëve").</li>
                            <li>Kërkoni për një buton ose ikonë "Eksporto" (shpesh e përfaqësuar nga një shigjetë që tregon jashtë ose një ikonë shkarkimi).</li>
                            <li>Zgjidhni formatin tuaj të dëshiruar të eksportimit (p.sh., CSV, Excel, PDF).</li>
                            <li>Klikoni "Eksporto" për të shkarkuar skedarin e të dhënave në kompjuterin tuaj.</li>
                        </ol>
                        <p>Opsionet dhe formatet specifike të eksportimit mund të ndryshojnë në varësi të seksionit të aplikacionit.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingFifteen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingFifteen" aria-expanded="false" aria-controls="headingFifteen">
                            <i class="fas fa-question me-2 text-info"></i> A ka një aplikacion celular për këtë platformë?
                        </button>
                    </h2>
                </div>
                <div id="headingFifteen" class="collapse" aria-labelledby="headingFifteen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Po, ne ofrojmë një aplikacion celular të dedikuar për platformën tonë! Aplikacioni ynë celular është i disponueshëm për pajisjet iOS dhe Android. Mund ta shkarkoni nga App Store ose Google Play Store duke kërkuar për "Emri i Aplikacionit Tuaj Celular". Aplikacioni celular ofron shumicën e veçorive të disponueshme në versionin ueb, me një ndërfaqe të optimizuar për përdorim në pajisje mobile. </p>
                        <p>Përveç aplikacionit celular, ne gjithashtu ofrojmë një **API (Application Programming Interface)** që lejon zhvilluesit të integrohen me platformën tonë. Dokumentacioni i API-së është i disponueshëm në <a href="#">Faqja e Dokumentacionit API</a>. </p>
                        <p>Gjithashtu, për udhëzime vizuale dhe mësime, ju mund të gjeni një numër të madh të përmbajtjeve tona në **YouTube**. Vizitoni kanalin tonë <a href="#">Kanali ynë YouTube</a> dhe kërkoni përmbajtje duke përdorur **Content ID** të caktuar për veçoritë ose temat specifike për të cilat keni nevojë për ndihmë.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingSixteen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingSixteen" aria-expanded="false" aria-controls="headingSixteen">
                            <i class="fas fa-question me-2 text-info"></i> Sa shpesh bëhet kopja rezervë e të dhënave?
                        </button>
                    </h2>
                </div>
                <div id="headingSixteen" class="collapse" aria-labelledby="headingSixteen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Siguria dhe integriteti i të dhënave janë prioritetet tona kryesore. Ne kryejmë kopje rezervë automatike të të dhënave çdo ditë për të siguruar që të dhënat tuaja të jenë të sigurta dhe të rikuperueshme në rast të ndonjë problemi të paparashikuar. Kopjet rezervë ruhen në mënyrë të sigurt në lokacione gjeografikisht të ndara.</p>
                        <p>Për detaje specifike mbi politikën tonë të kopjeve rezervë dhe periudhat e mbajtjes, ju lutemi kontaktoni ekipin tonë të mbështetjes IT.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingSeventeen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingSeventeen" aria-expanded="false" aria-controls="headingSeventeen">
                            <i class="fas fa-question me-2 text-info"></i> Ku mund të gjej tutoriale dhe udhëzues video?
                        </button>
                    </h2>
                </div>
                <div id="headingSeventeen" class="collapse" aria-labelledby="headingSeventeen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Ne kemi një koleksion tutorialesh video dhe udhëzuesish për t'ju ndihmuar të nxirrni më të mirën nga platforma. Ju mund t'i gjeni ato në <a href="#">Faqja e Tutorialeve Video</a> ose në <a href="#">Kanalin tonë zyrtar YouTube</a>. Këto burime mbulojnë një gamë të gjerë temash, nga lundrimi bazë deri te përdorimi i avancuar i veçorive.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingEighteen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingEighteen" aria-expanded="false" aria-controls="headingEighteen">
                            <i class="fas fa-question me-2 text-info"></i> Si të ftoj anëtarët e ekipit të bashkohen me llogarinë tonë?
                        </button>
                    </h2>
                </div>
                <div id="headingEighteen" class="collapse" aria-labelledby="headingEighteen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Për të ftuar anëtarët e ekipit në llogarinë tuaj, zakonisht ju nevojiten privilegje administratori. Nëse keni lejet e nevojshme:</p>
                        <ol>
                            <li>Shkoni në seksionin "Menaxhimi i Përdoruesve" ose "Cilësimet e Ekipit".</li>
                            <li>Klikoni "Fto Përdorues" ose "Shto Anëtar Ekipi".</li>
                            <li>Fusni adresat email të anëtarëve të ekipit që dëshironi të ftoni.</li>
                            <li>Caktoni atyre role dhe leje të përshtatshme.</li>
                            <li>Dërgoni ftesën. Ata do të marrin një email me udhëzime se si të bashkohen me llogarinë tuaj.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingNineteen">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingNineteen" aria-expanded="false" aria-controls="headingNineteen">
                            <i class="fas fa-question me-2 text-info"></i> Cilat janë rolet dhe lejet e ndryshme të përdoruesve?
                        </button>
                    </h2>
                </div>
                <div id="headingNineteen" class="collapse" aria-labelledby="headingNineteen" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Platforma përdor një sistem kontrolli aksesi të bazuar në role për të menaxhuar lejet e përdoruesve. Rolet e zakonshme të përdoruesve përfshijnë:</p>
                        <ul>
                            <li>**Administratori:** Akses i plotë në të gjitha veçoritë dhe cilësimet, duke përfshirë menaxhimin e përdoruesve dhe konfigurimin e sistemit.</li>
                            <li>**Menaxheri:** Akses në shumicën e veçorive, shpesh duke përfshirë raportimin, menaxhimin e të dhënave, por akses i kufizuar në cilësimet e sistemit.</li>
                            <li>**Përdoruesi/Përdoruesi Standard:** Akses në funksionalitetet kryesore që lidhen me detyrat e tyre të përditshme, me kufizime në funksionet administrative.</li>
                            <li>**Shikuesi/Vetëm për Lexim:** Kufizohet në shikimin e të dhënave dhe raporteve, pa aftësi redaktimi ose administrative.</li>
                        </ul>
                        <p>Rolet dhe lejet specifike mund të personalizohen nga administratori i sistemit bazuar në nevojat e organizatës suaj.</p>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm rounded mb-3">
                <div class="card-header bg-light" id="headingTwenty">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#headingTwenty" aria-expanded="false" aria-controls="headingTwenty">
                            <i class="fas fa-question me-2 text-info"></i> Si të zgjidh problemet e logimit?
                        </button>
                    </h2>
                </div>
                <div id="headingTwenty" class="collapse" aria-labelledby="headingTwenty" data-bs-parent="#helpAccordion">
                    <div class="card-body">
                        <p>Nëse po përjetoni probleme me logimin, ju lutemi provoni hapat e mëposhtëm për zgjidhjen e problemeve:</p>
                        <ol>
                            <li>**Kontrolloni emrin tuaj të përdoruesit dhe fjalëkalimin:** Sigurohuni që po fusni kredencialet e sakta. Fjalëkalimet janë të ndjeshme ndaj shkronjave të mëdha dhe të vogla.</li>
                            <li>**Caps Lock:** Sigurohuni që Caps Lock nuk është aktivizuar aksidentalisht.</li>
                            <li>**Cache dhe Cookies të Shfletuesit:** Pastroni cache-in dhe cookies e shfletuesit tuaj, pastaj provoni të logoheni përsëri.</li>
                            <li>**Linku "Harruat Fjalëkalimin":** Përdorni linkun "Harruat Fjalëkalimin" në faqen e logimit për të rivendosur fjalëkalimin tuaj nëse është e nevojshme.</li>
                            <li>**Kontaktoni Mbështetjen:** Nëse ende nuk mund të logoheni, kontaktoni ekipin tonë të mbështetjes për ndihmë.</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .main-content {
            padding: 2rem;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in-out;
        }

        .slide-up {
            animation: slideUp 0.6s ease-out;
        }

        .card {
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn-link {
            text-decoration: none;
            color: #495057;
            font-weight: 500;
            padding: 0;
            box-shadow: none;
        }

        .btn-link:hover,
        .btn-link:focus {
            text-decoration: none;
            box-shadow: none;
            color: #007bff;
        }

        .btn-link:focus {
            outline: none;
        }

        .accordion .card:not(:first-of-type) .card-header:not(.bg-light) {
            border-top: 0;
        }


        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</body>

</html>