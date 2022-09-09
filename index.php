<?php
// Функция для определения окончаний слов
// в зависимости от числа
function num_word($value, $words): string
{
    $num = $value % 100;
    if ($num > 19) {
        $num = $num % 10;
    }

    $out = match ($num) {
        1 => $words[0],
        2, 3, 4 => $words[1],
        default => $words[2],
    };

    return $out;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // Проверка на валидность введенного имени
    $name = $_POST['name'];
    if(!isset($name)) $errors['name'] = 'Вы не указали имя';
    preg_match('~^([\p{L}]+)$~u', $name, $matches);
    if(empty($matches)) $errors['name'] = 'Именем должно быть одно буквенное слово';

    // Проверка на валидность введенного номера телефона
    $phone = $_POST['phone'];
    if(!isset($phone)) $errors['phone'] = 'Вы не указали номер телефона';
    preg_match('~^(\+[0-9]{12})$~', $phone, $matches);
    if(empty($matches)) $errors['phone'] = 'Номер должен начинаться с + и количество чисел должно ровняться 12-ти';

    // Проверка на валидность введенного адреса электронной почты
    $email = $_POST['email'];
    if(!isset($email)) $errors['email'] = 'Вы не указали адрес электронной почты';
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Вы указали невалидный адрес электронной почты';

    // Проверка на валидность введенного количества тактов
    $tacts = $_POST['tacts'];
    if(!isset($tacts)) $errors['tacts'] = 'Вы не указали количество тактов';
    $tacts = intval($tacts);
    if($tacts < 1) $errors['tacts'] = 'Вы указали неверное количество тактов (минимум 1)';

    // Проверка на валидность выбранного типа размножения
    $type = $_POST['type'];
    if(!isset($type)) $errors['type'] = 'Вы не указали тип размножения';
    $type = intval($type);
    if($type != 1 && $type != 2) $errors['type'] = 'Вы указали неверный тип размножения';

    // Если ошибок нет, то ...
    if(empty($errors)) {
        if($type == 1){
            // Тип размножения постепенный
            $green_bacteria = 1;
            $red_bacteria = 1;

            for($i = 1; $i <= $tacts; $i++) {
                // green
                $red_bacteria += $green_bacteria * 4;
                $green_bacteria *= 3;

                // red
                $green_bacteria += $red_bacteria * 7;
                $red_bacteria *= 5;
            }
        } else {
            // Тип размножения одновременный
            $green_bacteria = 1;
            $red_bacteria = 1;

            for($i = 1; $i <= $tacts; $i++) {
                $old_green_bacteria = $green_bacteria;
                $old_red_bacteria = $red_bacteria;

                // green
                $green_bacteria = $old_green_bacteria * 3;
                $red_bacteria = $old_green_bacteria * 4;
                
                // red
                $green_bacteria += $old_red_bacteria * 7;
                $red_bacteria += $old_red_bacteria * 5;
            }
        }

        if($type == 1) $type_view = 'Постепенное размножение';
        else $type_view = 'Одновременное размножение';

        // Упаковка результата для показа на странице
        $result = [
            'info' => [
                'name' => $name,
                'phone' => $phone,
                'email' => $email
            ],
            'bacteria' => [
                'green' => $green_bacteria,
                'red' => $red_bacteria,
                'tacts' => $tacts,
                'type' => $type_view
            ]
        ];
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@500&display=swap" rel="stylesheet">
    <title>CalcBacteria</title>
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
</head>
<body style="font-family: 'Inter', sans-serif;background: url('/assets/img/bg.jpg');background-size: cover;backdrop-filter: blur(10px);-webkit-backdrop-filter: blur(10px);">
    <div class="flex min-h-full h-screen items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="w-full max-w-md m-auto space-y-2">
            <?php if(isset($errors)): ?>
                <?php foreach($errors as $error): ?>
                    <div class="rounded-2xl text-white text-sm shadow-md py-2 px-3" style="background-color:rgba(220, 38, 38, 0.5);">
                        <?=$error?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php if(isset($result)): ?>
                <div class="rounded-2xl shadow-lg p-5" style="background-color:rgba(0, 0, 0, 0.5);">
                    <div class="text-center">
                        <h2 class="text-2xl text-green-500 font-bold">CalcBacteria</h2>
                        <p class="text-gray-200 text-sm">Подсчёт завершен &bull; <?=$result['bacteria']['type']?></p>
                    </div>
                    <hr style="border: none; height: 2px;" class="my-5 text-green-900 rounded-lg bg-green-900">
                    <p class="text-gray-200 text-sm">за <?=$result['bacteria']['tacts']?> тактов выработались:</p>
                    <div class="mt-3">
                        <span class="text-green-500 text-5xl font-bold"><?=$result['bacteria']['green']?></span> <span class="text-green-500 text-2xl"><?=num_word($result['bacteria']['green'], ['зелёная бактерия', 'зелёные бактерии', 'зелёных бактерий'])?></span>
                    </div>
                    <div class="mt-5">
                        <span class="text-red-500 text-5xl font-bold"><?=$result['bacteria']['red']?></span> <span class="text-red-500 text-2xl"><?=num_word($result['bacteria']['red'], ['красная бактерия', 'красные бактерии', 'красных бактерий'])?></span>
                    </div>
                    <hr style="border: none; height: 2px;" class="my-5 text-green-900 rounded-lg bg-green-900">
                    <div>
                        <table class="table-auto w-full text-gray-200 text-sm">
                            <tbody>
                                <tr>
                                    <td>Имя:</td>
                                    <td class="font-bold"><?=$result['info']['name']?></td>
                                </tr>
                                <tr>
                                    <td>Номер телефона:</td>
                                    <td class="font-bold"><?=$result['info']['phone']?></td>
                                </tr>
                                <tr>
                                    <td>Адрес электронной почты:</td>
                                    <td class="font-bold"><?=$result['info']['email']?></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-5">
                            <a href="/" class="group shadow hover:shadow-md transition duration-300 relative flex w-full justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Подсчитать ещё раз
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="rounded-2xl shadow-lg p-5" style="background-color:rgba(0, 0, 0, 0.5);">
                    <div class="text-center">
                        <h2 class="text-2xl text-green-500 font-bold">CalcBacteria</h2>
                        <p class="text-gray-200 text-sm">Укажите необходимые данные, чтобы начать.</p>
                    </div>
                    <hr style="border: none; height: 2px;" class="my-5 text-green-900 rounded-lg bg-green-900">
                    <form method="POST" class="text-center">
                        <div>
                            <input name="name" type="text" required class="bg-zinc-900 relative block w-full appearance-none rounded-none rounded-t-md border border-zinc-800 px-3 py-2 text-gray-200 placeholder-gray-500 focus:z-10 focus:border-green-500 focus:outline-none focus:ring-green-500 sm:text-sm" placeholder="Ваше имя">
                        </div>
                        <div>
                            <input name="phone" type="text" required class="bg-zinc-900 relative block w-full appearance-none rounded-none border border-zinc-800 px-3 py-2 text-gray-200 placeholder-gray-500 focus:z-10 focus:border-green-500 focus:outline-none focus:ring-green-500 sm:text-sm" placeholder="Ваш номер телефона">
                        </div>
                        <div>
                            <input name="email" type="email" required class="bg-zinc-900 relative block w-full appearance-none rounded-none border border-zinc-800 px-3 py-2 text-gray-200 placeholder-gray-500 focus:z-10 focus:border-green-500 focus:outline-none focus:ring-green-500 sm:text-sm" placeholder="Ваш адрес электронной почты">
                        </div>
                        <div>
                            <input name="tacts" type="number" required class="bg-zinc-900 relative block w-full appearance-none rounded-none border border-zinc-800 px-3 py-2 text-gray-200 placeholder-gray-500 focus:z-10 focus:border-green-500 focus:outline-none focus:ring-green-500 sm:text-sm" placeholder="Количество тактов" min="1">
                        </div>
                        <div>
                            <select name="type" class="bg-zinc-900 border border-zinc-800 text-gray-200 text-sm rounded-lg rounded-none rounded-b-md focus:ring-green-500 focus:border-green-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-green-500 dark:focus:border-green-500">
                                <option selected value="1">Постепенное размножение</option>
                                <option value="2">Одновременное размножение</option>
                            </select>
                        </div>
                        <div class="mt-5 text-gray-300 text-xs text-left">
                        Есть два вида бактерий. Одни — зеленые. Вторые — красные.<br>
                        Каждый такт времени зеленая бактерия превращается в 3 зеленые и 4 красные. Каждая красная бактерия превращается в 7 зеленых и 5 красных.<br>
                        Вам нужно указать количество тактов и остальную информацию.<br><br>
                        Постепенное размножение — размножаются сначала зелёные, потом красные.<br>
                        Одновременное размножение — зелёные и красные размножаются одновременно.
                        </div>
                        <div class="mt-5">
                            <button type="submit" class="group shadow hover:shadow-md transition duration-300 relative flex w-full justify-center rounded-md border border-transparent bg-green-600 py-2 px-4 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                Начать подсчёт
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>