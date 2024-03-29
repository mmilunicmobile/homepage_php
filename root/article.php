<?php
    include '../Parsedown.php';
    $article_path = $_GET["article"];
    $xml = simplexml_load_file(__DIR__ . "/../articles.xml");

    $meta_article = null;

    $i = 0;

    foreach ($xml->article as $article) {
        if ($article->docname == $article_path) {
            $meta_article = $article;
            break;
        }
        $i += 1;
    }

    $text = "";
    $article_name_sanitized = htmlspecialchars($article_path);

    if (!isset($meta_article)) {
        $text = <<<END
            # 404
            looks like the article ur tryin to find can't be found!
            doesn't mean i wont add "$article_name_sanitized" someday,
            just that it isn't here today. Maybe you would enjoy some of
            my other stuff on my [homepage](/)!
            END;
    } else {
        if ($meta_article->python == true) {
            $text = shell_exec("python3 " . __DIR__ . "/../articles/" . $meta_article->docname); 
        } else {
            $my_file = fopen(__DIR__ . "/../articles/" . $meta_article->docname, "r") or die("oops, there was an error 500");
            $text = fread(
                $my_file, 
                filesize(__DIR__ . "/../articles/" . $meta_article->docname)
            );
            fclose($my_file);
        }
    }
    if ($i != 0) {
        $next_link = "/article.php?article=" . $xml->article[$i - 1]->docname;
    } else {
        $next_link = "";
    }

    if ($i + 1 != $xml->article->count()) {
        $last_link = "/article.php?article=" . $xml->article[$i + 1]->docname;
    } else {
        $last_link = "";
    }
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $meta_article->name . " - Mattsite"?></title>
        <link rel="stylesheet" href="styles.css">
        <script>window.MathJax = {
            tex: {
                inlineMath: [['$','$'], ['\\(', '\\)']]
            }
        }</script>
        <script src="https://polyfill.io/v3/polyfill.min.js?features=es6"></script>
        <script type="text/javascript" id="MathJax-script" async
            src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js">
        </script>
    </head>
    <body>
        <?php
            
            if (isset($meta_article) && $meta_article->skip_header != true) {
                $heading_text = "# $meta_article->name\n";

                if (isset($meta_article->author)) {
                    $heading_text .= "By $meta_article->author\n\n";
                }

                if (isset($meta_article->date)) {
                    $heading_text .= "$meta_article->date\n\n";
                }

                $heading_text .= "---\n\n";

                $text = $heading_text . $text;
            }

            $Parsedown = new Parsedown();
            echo $Parsedown->text($text);
        ?>
        <div class="navbar">
            <<?php $closingTag = ($i + 1 == $xml->article->count()) ? 'span' : 'a'; echo $closingTag;?> href="<?php echo $last_link?>">Last</<?php echo $closingTag;?>>
            <a href="/">Home</a>
            <<?php $closingTag = ($i == 0) ? 'span' : 'a'; echo $closingTag;?> href="<?php echo $next_link?>">Next</<?php echo $closingTag;?>>
        </div>
    </body>
</html>
