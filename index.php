<!DOCTYPE html>
<html lang="sl">

<head>
    <title>Kocke</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css?v=6">
    <link rel="icon" type="image/x-icon" href="img/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h1 class="naslov">IGRA KOCK</h1>
    <form action="index2.php" method="post">
        <div class="vpis">
            <div class="igralci-vrstica">
                <div class="igralec">
                    <h3>IGRALEC 1</h3>
                    <input type="text" class="vpisnaPolja" name="ime1" placeholder="Uporabnisko ime" required maxlength="15">
                </div>
                <div class="igralec">
                    <h3>IGRALEC 2</h3>
                    <input type="text" class="vpisnaPolja" name="ime2" placeholder="Uporabnisko ime" required maxlength="15">
                </div>
                <div class="igralec">
                    <h3>IGRALEC 3</h3>
                    <input type="text" class="vpisnaPolja" name="ime3" placeholder="Uporabnisko ime" required maxlength="15">
                </div>
            </div>

            <div class="spodnja-vrstica">
                <div class="nastavitve">
                    <label>
                        Število kock
                        <select name="steviloKock" class="vpisnaPolja">
                            <option value="1">1 kocka</option>
                            <option value="2">2 kocki</option>
                            <option value="3" selected>3 kocke</option>
                        </select>
                    </label>
                    <label>
                        Število krogov
                        <select name="steviloKrogov" class="vpisnaPolja">
                            <option value="1">1 krog</option>
                            <option value="2">2 kroga</option>
                            <option value="3">3 krogi</option>
                            <option value="4">4 krogi</option>
                            <option value="5">5 krogov</option>
                            <option value="6">6 krogov</option>
                            <option value="7">7 krogov</option>
                            <option value="8">8 krogov</option>
                            <option value="9" selected>9 krogov</option>
                        </select>
                    </label>
                </div>
                <input type="submit" class="gumb" value="IGRAJ">
            </div>
        </div>
    </form>
</body>

</html>
