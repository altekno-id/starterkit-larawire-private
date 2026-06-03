<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <table>
        <tr>
            <td>Nama</td>
            <td>Umur</td>
        </tr>

        <?php
            foreach (range(1, 5) as $i) {
        ?>
                <tr>
                    <td><?php ?></td>
                    <td>30</td>
                </tr>
        <?php
            }
        ?>

    </table>
</body>
</html>
