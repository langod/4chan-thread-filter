<?php

@session_start();

if (empty($_SESSION['boards'])) {
    $_SESSION['boards'] = array_map(
        function ($item) {
            return $item->board;
        },
        json_decode(file_get_contents("http://api.4chan.org/boards.json"))->boards
    );
}

if ($_POST) {
    $_SESSION['words'] = array_map(
        function ($item) {
            return trim($item);
        },
        explode(',', $_POST['words'])
    );

    $_SESSION['checkedBoards'] = $_POST["boards"] ? array_keys($_POST["boards"]) : array();

    header('Location: index.php');
}

$words = !empty($_SESSION['words']) ? $_SESSION['words'] : array();
$boards = !empty($_SESSION['checkedBoards']) ? $_SESSION['checkedBoards'] : array();


$boardCatalogs = array();
$results = array();

if (!empty($words[0]) && !empty($boards[0])) {
    foreach ($boards as $board) {
        $boardCatalogs[$board] = json_decode(file_get_contents("http://api.4chan.org/{$board}/catalog.json"));
    }

    foreach ($boardCatalogs as $board => $catalog) {
        foreach ($catalog as $page) {
            foreach ($page->threads as $thread) {

                if (empty($thread->com)) {
                    continue;
                }

                foreach ($words as $word) {
                    if (preg_match("/\b$word\b/i", $thread->com) != FALSE) {
                        $results[$thread->no] = array(
                            "url" => "http://boards.4chan.org/{$board}/res/{$thread->no}",
                            "img" => "http://images.4chan.org/{$board}/src/{$thread->tim}{$thread->ext}",
                            "content" => $thread->com,
                        );
                    }
                }
            }
        }
    }
}

?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form action="" method="POST">
    <ul id="boards">
        <?php foreach ($_SESSION['boards'] as $i => $board): ?>
            <li>
                <label for="boards[<?php echo $board ?>]"><?php echo $board ?></label>
                <input type="checkbox" name="boards[<?php echo $board ?>]" <?php echo in_array($board, $boards) ? "checked" : ""; ?>>
            </li>
        <?php endforeach; ?>
    </ul>
    <label for="words">Words</label>
    <input type="text" name="words" id="words" value="<?php echo implode(', ', $words); ?>">
    <input type="submit" value="submit">
</form>
<ul id="threads">
    <?php foreach ($results as $result): ?>
        <li>
            <a href="<?php echo $result['url'] ?>" target="_blank">
                <img src="<?php echo $result['img'] ?>" alt="">
            </a>

            <div><?php echo $result['content'] ?></div>
        </li>
    <?php endforeach; ?>
</ul>
<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="js/jquery.masonry.min.js"></script>
<script type="text/javascript" src="js/app.js"></script>
</body>
</html>
 
