<?php
class HtmlElement {
    private $type; // h1, h2, h3, p, etc.
    private $unaryTags = array('input', 'img', 'hr', 'br', 'meta', 'link');
    private $attributes;
    private $content;

    public function HtmlElement($type, $attributes = array()) {
        $this->type = strtolower($type);

        if(isset($attributes['text'])) {
            $this->content = $attributes['text'];
            unset($attributes['text']);
        }

        foreach($attributes as $attribute => $value) {
            $this->attr($attribute, $value);
        }

        return $this;
    }

    // Shortcut methods
    function head($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('head', $options); }
    function title($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('title', $options); }
    function script($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('script', $options); }
    function javaScript($options = array()) {
        if(!is_array($options)) $options = array('src' => $options);
        $options['type'] = 'text/javascript';
        $options['language'] = 'javascript';
        return new HtmlElement('script', $options);
    }
    function css($options = array()) {
        if(!is_array($options)) $options = array('href' => $options);
        $options['rel'] = 'stylesheet';
        $options['type'] = 'text/css';
        return new HtmlElement('link', $options);
    }
    function body($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('body', $options); }
    function header($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('header', $options); }
    function nav($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('nav', $options); }
    function footer($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('footer', $options); }
    function h1($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('h1', $options); }
    function h2($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('h2', $options); }
    function h3($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('h3', $options); }
    function h4($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('h4', $options); }
    function h5($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('h5', $options); }
    function h6($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('h6', $options); }
    function p($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('p', $options); }
    function a($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('a', $options); }
    function ul($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('ul', $options); }
    function ol($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('ol', $options); }
    function li($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('li', $options); }
    function span($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('span', $options); }
    function div($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('div', $options); }
    function img($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('img', $options); }
    function table($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('table', $options); }
    function tr($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('tr', $options); }
    function td($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('td', $options); }
    function meta($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('meta', $options); }
    function link($options = array()) { if(!is_array($options)) $options = array('text' => $options); return new HtmlElement('link', $options); }
    
    public static function tableFromArray($array, $options = array()) {
        $table = HtmlElement::table();
        $thead = new HtmlElement('thead');
        $tbody = new HtmlElement('tbody');
        $tfoot = new HtmlElement('tfoot');
        
        $headAndFootColumns = new HtmlElement('tr');
        foreach(Arr::first($array) as $key => $value) {
            $td = HtmlElement::td(String::camelCaseToRegular($key));
            $headAndFootColumns->append($td);
        }
        $thead->append($headAndFootColumns);
        $tfoot->append($headAndFootColumns);

        foreach($array as $column) {
            $tr = new HtmlElement('tr');
            
            foreach($column as $value) {
                $td = HtmlElement::td($value);
                $tr->append($td);
            }
            
            $tbody->append($tr);
        }
        $table->append($thead.$tbody.$tfoot);
                
        return $table;
    }

    function attr($attribute, $value = null, $append = false) {
        if($value === null && $append === false && isset($this->attributes[$attribute])) {
            return $this->attributes[$attribute];
        }
        else if($value === null && $append === false && !isset($this->attributes[$attribute])) {
            return null;
        }

        if($append) {
            if(isset($this->attributes[$attribute])) {
                $currentValue = $this->attributes[$attribute];
            }
            else {
                $currentValue = '';
            }
            $this->attributes[$attribute] = $currentValue.$value;
        }
        else {
            if(!is_array($attribute)) {
                $this->attributes[$attribute] = $value;
            }
            else {
                $this->attributes = array_merge($this->attributes, $attribute);
            }
        }

        return $this;
    }

    function removeAttr($attribute) {
        if(isset($this->attributes[$attribute])) {
            unset($this->attributes[$attribute]);
        }

        return $this;
    }

    function clearAttrs() {
        $this->attributes = array();

        return $this;
    }

    function addClass($class) {
        $currentClasses = $this->attr('class');
        //print_r($currentClasses); exit();

        // Check to see if the class is already added
        if(!strstr($currentClasses, $class)) {
            $newClasses = $currentClasses.' '.$class;
            $this->attr('class', String::trim($newClasses));
        }
        
        return $this;
    }

    function append($object = null) {
        if($object === null) {
            return $this;
        }

        if(!is_array($this->content)) {
            $this->content = array($this->content);
        }

        $this->content[] = $object;

        return $this;
    }
    
    function prepend($object = null) {
        if($object === null) {
            return $this;
        }

        if(!is_array($this->content)) {
            $this->content = array($this->content);
        }

        Arr::unshift($object, $this->content);

        return $this;
    }

    function text($text) {
        $this->content = $text;

        return $this;
    }

    function find($selector) {
        
    }
    
    function build($content) {
        $response = '';
        if(is_array($content)) {
            foreach($content as $object) {
                $response .= $this->build($object);
            }
        }
        else if(is_object($content) && get_class($content) == 'HtmlElement') {
            if(isset($content->data)) {
                $response .= $content->data->html();
            }
            else {
                $response .= $content->html();
            }
        }
        else {
            $response .= $content;
        }
        
        return $response;
    }

    function html($html = null) {
        if($html !== null) {
            $this->content = $html;
            return $this;
        }

        // Start the tag
        $element = '<'.$this->type;

        // Add attributes
        if(count($this->attributes)) {
            foreach($this->attributes as $key => $value) {
                $element .= ' '.$key.'="'.$value.'"';
            }
        }

        // Close the element
        if(!in_array($this->type, $this->unaryTags)) {
            $element.= '>'.$this->build($this->content).'</'.$this->type.'>';
        }
        else {
            $element.= ' />';
        }

        // Don't format the XML string, saves time
        //return $this->formatXmlString($element);
        return $element;
    }

    // Doing preg_matches on every line slows things down significantly!
    static function formatXmlString($xml) {

        // add marker linefeeds to aid the pretty-tokeniser (adds a linefeed between all tag-end boundaries)
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        // now indent the tags
        $token = strtok($xml, "\n");
        $result = ''; // holds formatted version as it is built
        $pad = 0; // initial indent
        $indent = 0;
        $matches = array(); // returns from preg_matches()
        // scan each line and adjust indent based on opening/closing tags
        while($token !== false) :

            // test for the various tag states
            // 1. open and closing tags on same line - no change
            if(preg_match('/.+<\/\w[^>]*>$/', $token, $matches)) :
                $indent = 0;
            // 2. closing tag - outdent now
            elseif(preg_match('/^<\/\w/', $token, $matches)) :
                $pad--;
            // 3. opening tag - don't pad this one, only subsequent tags
            elseif(preg_match('/^<\w[^>]*[^\/]>.*$/', $token, $matches)) :
                $indent++;
            // 4. no indentation needed
            else :
                $indent = 0;
            endif;

            // pad the line with the required number of leading spaces
            $line = str_pad($token, strlen($token) + $pad, ' ', STR_PAD_LEFT);
            $result .= $line . "\n"; // add to the cumulative result, with linefeed
            $token = strtok("\n"); // get the next token
            $pad += $indent; // update the pad size for subsequent lines
        endwhile;

        return $result;
    }

    /**
     * Echoes out the element
     *
     * @return <type>
     */
    function __toString() {
        return $this->html();
    }

    function toString() {
        return $this->__toString();
    }
    
    static function pagination($startItemOffset, $itemsPerPage, $totalItemCount, $url, $className = 'pagination', $pageNumbers = false) {
        $ul = new HtmlElement('ul', array('class' => $className));

        $currentPageNumber = ceil(($startItemOffset) / $itemsPerPage);
        if($currentPageNumber == 0) {
            $currentPageNumber = 1;
        }
        //echo '<br />start item'.$startItemOffset.'<br />';
        //echo 'items per page'.$itemsPerPage.'<br />';
        //echo 'current page'.$currentPageNumber.'<br />';
        $nextPageNumber = $currentPageNumber + 1;
        $currentPageFirstItemOffset = ($currentPageNumber * $itemsPerPage) - $itemsPerPage + 1;

        $totalPageCount = ceil($totalItemCount / $itemsPerPage);

        //echo $totalItemCount;
        //echo $itemsPerPage;
        //echo $totalPageCount;

        $lastPageNumber = $totalPageCount;
        $lastPageFirstItemOffset = $totalItemCount - ($totalItemCount % $itemsPerPage) + 1;

        // Previous button
        if($currentPageNumber > 1) {
            $previousPageFirstItemOffset = $currentPageFirstItemOffset - $itemsPerPage;
            if($pageNumbers) {
                $ul->append('<li class="previousPage"><a href="'.(str_replace('[offset]', $currentPageNumber - 1, $url)).'">Prev</a></li>');
            }
            else {
                $ul->append('<li class="previousPage"><a href="'.(str_replace('[offset]', $previousPageFirstItemOffset, $url)).'">Prev</a></li>');
            }

        }

        // Less than the fifth page
        if($currentPageNumber < 10) {
            for($tempPageNumber = 1; $tempPageNumber <= $totalPageCount && $tempPageNumber <= 10; $tempPageNumber++) {
                if($tempPageNumber == $currentPageNumber) {
                    $class = ' active';
                }
                else {
                    $class = '';
                }
                $tempPageFirstItemOffset = ($tempPageNumber * $itemsPerPage) - $itemsPerPage + 1;
                if($pageNumbers) {
                    $ul->append('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageNumber, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                else {
                    $ul->append('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageFirstItemOffset, $url)).'">'.$tempPageNumber.'</a></li>');
                }
            }

            if($lastPageNumber > 10) {
                $ul->append('<li class="pageSeparator">...</li>');
                if($pageNumbers) {
                    $ul->append('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageNumber, $url)).'">'.$lastPageNumber.'</a></li>');
                }
                else {
                    $ul->append('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageFirstItemOffset, $url)).'">'.$lastPageNumber.'</a></li>');
                }
            }
        }
        // Inbetween the fifth and last five pages
        else if($currentPageNumber >= 10 && $currentPageNumber <= $totalPageCount - 10) {
            $ul->append('<li class="pageNumber"><a href="'.(str_replace('[offset]', '1', $url)).'">1</a></li>');
            $ul->append('<li class="pageSeparator">...</li>');

            $tempPageLowerLimit = $currentPageNumber - 3;
            $tempPageUpperLimit = $currentPageNumber + 3;
            for($tempPageNumber = $tempPageLowerLimit; $tempPageNumber <= $tempPageUpperLimit; $tempPageNumber++) {
                if($tempPageNumber == $currentPageNumber) {
                    $class = ' active';
                }
                else {
                    $class = '';
                }
                $tempPageFirstItemOffset = ($tempPageNumber * $itemsPerPage) - $itemsPerPage + 1;
                if($pageNumbers) {
                    $ul->append('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageNumber, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                else {
                    $ul->append('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageFirstItemOffset, $url)).'">'.$tempPageNumber.'</a></li>');
                }

            }

            $ul->append('<li class="pageSeparator">...</li>');

            if($pageNumbers) {
                $ul->append('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageNumber, $url)).'">'.$lastPageNumber.'</a></li>');
            }
            else {
                $ul->append('<li class="pageNumber"><a href="'.(str_replace('[offset]', $lastPageFirstItemOffset, $url)).'">'.$lastPageNumber.'</a></li>');
            }
        }
        // Within the last five pages
        else if($currentPageNumber > $totalPageCount - 10) {
            if($currentPageNumber > 10) {
                $ul->append('<li class="pageNumber"><a href="'.(str_replace('[offset]', '1', $url)).'">1</a></li>');
                $ul->append('<li class="pageSeparator">...</li>');
            }

            for($tempPageNumber = $totalPageCount - 9; $tempPageNumber <= $totalPageCount; $tempPageNumber++) {
                if($tempPageNumber == $currentPageNumber) {
                    $class = ' active';
                }
                else {
                    $class = '';
                }
                $tempPageFirstItemOffset = ($tempPageNumber * $itemsPerPage) - $itemsPerPage + 1;

                if($pageNumbers) {
                    $ul->append('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageNumber, $url)).'">'.$tempPageNumber.'</a></li>');
                }
                else {
                    $ul->append('<li class="pageNumber'.$class.'"><a href="'.(str_replace('[offset]', $tempPageFirstItemOffset, $url)).'">'.$tempPageNumber.'</a></li>');
                }
            }
        }

        // Next button
        //echo 'Current page number: '.$currentPageNumber.'<br />';
        if($currentPageNumber < $totalPageCount) {
            $nextPageFirstItemOffset = $currentPageFirstItemOffset + $itemsPerPage;
            if($pageNumbers) {
                $ul->append('<li class="nextPage"><a href="'.(str_replace('[offset]', $currentPageNumber + 1, $url)).'">Next</a></li>');
            }
            else {
                $ul->append('<li class="nextPage"><a href="'.(str_replace('[offset]', $nextPageFirstItemOffset, $url)).'">Next</a></li>');
            }
        }

        return $ul;
    }

}

?>
