<!DOCTYPE html>
<?php
    header('Content-Type: text/html; charset=Windows-1251');
?>
<html>
    <head>
        <meta charset="Windows-1251">
        <title>Контрольная работа №3</title>
    </head>
    <body>
        <?php
            require "text.php";
            $text = iconv("UTF-8", "CP1251", $text);

            define('PARAGRAPHSINPAGE', 10);

            $controller = new Controller($text);
            $controller->run();
        ?>
    </body>
</html>

<?php
class Model{
    private $currentPage;
    private $text;
    private $numberOfPages;
    private $content;
    private $size;

    public function __construct($currentPage, $text)
    {
        $this->currentPage = $currentPage;
        $this->text = $text;
        $this->size = array();
    }

    public function returnParagraphsArray(){
        $paragraphsArray = explode("\r\n", $this->text);
        return $paragraphsArray;
    }

    public function computeNumberOfPages(){
        $numberParagraphs = count($this->returnParagraphsArray());

        $this->numberOfPages = ceil($numberParagraphs/PARAGRAPHSINPAGE);
    }

    public function correct(){
        if($this->currentPage <= 0 || $this->currentPage > $this->numberOfPages)
            die(iconv("UTF-8", "CP1251",'Неправильный ввод'));
    }

    public function setContent(){
        $this->content = array_slice($this->returnParagraphsArray(), ($this->currentPage-1)*PARAGRAPHSINPAGE, PARAGRAPHSINPAGE);
    }

    public function computeSize(){
        $i = 0;
        foreach($this->content as $paragraph){
            $paragraph = strip_tags($paragraph);
            $this->size[$i]['symbol'] = strlen($paragraph);
            $this->size[$i]['word'] = str_word_count($paragraph);
            $i++;
        }
    }

    public function setColor(){
        $patternJava = '/(j)(a)(v)(a)/i';
        $patternHTML = '/(h)(t)(m)(l)/i';
        $patternPHP = '/(p)(h)(p)/i';
        $patternASP = '/(a)(s)(p)/i';
        $patternASPNET = '/(a)(s)(p)(.)(n)(e)(t)/i';

        $this->content = preg_replace($patternJava, '<span style="color: red;">$1$2$3$4</span>', $this->content);
        $this->content = preg_replace($patternHTML, '<span style="color: green;">$1$2$3$4</span>', $this->content);
        $this->content = preg_replace($patternPHP, '<span style="color: blue;">$1$2$3</span>', $this->content);
        $this->content = preg_replace($patternASP, '<span style="color: yellow">$1$2$3</span>', $this->content);
        $this->content = preg_replace($patternASPNET, '<span style="color: grey;">$1$2$3$4$5$6</span>', $this->content);
    }

    public function setFirstLetter(){
        $pattern = '/(^|[.!?]\s+)(<.*>)?([0-9,A-Z,a-z,А-Я,а-я,Ёё])/U';

        $replace = '$1$2<b>$3</b>';

        $this->content = preg_replace($pattern, $replace, $this->content);
    }

    public function getContent(){
        return $this->content;
    }

    public function getSize(){
        return $this->size;
    }

    public function getPage(){
        return $this->currentPage;
    }

    public function getNumberOfPages(){
        return $this->numberOfPages;
    }
}

class View{
    private $data;
    private $size;
    private $currentPage;
    private $numberOfPages;

    public function __construct($data, $size, $currentPage, $numberOfPages)
    {
        $this->data = $data;
        $this->size = array();
        $this->size = $size;
        $this->currentPage = $currentPage;
        $this->numberOfPages = $numberOfPages;

    }

    public function show(){
        foreach($this->data as $paragraph){
            echo '<p>' . $paragraph . '</p>';
        }

        function viewHref($hrefPage){
            echo ' <a href="script1.php?page=' . $hrefPage .'">' . $hrefPage . '</a> ';
        }
        echo '<p>';
        if ($this->currentPage > 2) viewHref(1);
        if ($this->currentPage > 3) echo '...';
        if ($this->currentPage > 1) viewHref($this->currentPage - 1);
        echo ' ' . $this->currentPage;
        if ($this->currentPage < $this->numberOfPages) viewHref($this->currentPage + 1);
        if ($this->currentPage < $this->numberOfPages - 2) echo '...';
        if ($this->currentPage < $this->numberOfPages - 1) viewHref($this->numberOfPages);
        echo '</p>';

        $i = 0;
        foreach($this->size as $value){
            echo '<p>';
            echo iconv("UTF-8","CP1251", 'Количество символов в абзаце ') . ++$i . ': ' . $value['symbol'] . '<br>';
            echo iconv("UTF-8","CP1251", 'Количество слов в абзаце ') . $i . ': ' . $value['word'] . '<br>';
            echo '</p>';
        }
    }


}

class Controller{
    private $model;
    private $view;
    private $currentPage;
    private $text;

    public function __construct($text)
    {
        $this->currentPage = empty($_GET['page']) ? 1 : intval($_GET['page']);
        $this->text = $text;
    }

    public function run(){
        $this->model = new Model($this->currentPage, $this->text);
        $this->model->computeNumberOfPages();
        $this->model->correct();
        $this->model->setContent();
        $this->model->computeSize();
        $this->model->setColor();
        $this->model->setFirstLetter();

        $this->view = new View($this->model->getContent(), $this->model->getSize(), $this->model->getPage(), $this->model->getNumberOfPages());
        $this->view->show();
    }
}