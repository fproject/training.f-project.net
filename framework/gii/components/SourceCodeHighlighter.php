<?php
class SourceCodeHighlighter {
    public static function formatPHPSourcecode($str, $return = false, $counting = true, $first_line_num = '1', $font_color = '#666')
    {
        $str = highlight_string($str, TRUE);
        $replace = array(
            '<font' => '<span',
            'color="' => 'style="color: ',
            '</font>' => '</span>',
            '<code>' => '',
            '</code>' => '',
            '<span style="color: #FF8000">' =>
                '<span style="color: '.$font_color.'">'
        );
        foreach ($replace as $html => $xhtml)
        {
            $str = str_replace($html, $xhtml, $str);
        }
        // delete the first <span style="color:#000000;"> and the corresponding </span>
        $str = substr($str, 30, -9);

        $arr_html      = explode('<br />', $str);
        $total_lines   = count($arr_html);
        $out           = '';
        $line_counter  = 0;
        $last_line_num = $first_line_num + $total_lines;

        foreach ($arr_html as $line)
        {
            $line = str_replace(chr(13), '', $line);
            $current_line = $first_line_num + $line_counter;
            if ($counting)
            {
                $out .= '<span style="color:'.$font_color.'">'
                    . str_repeat('&nbsp;', strlen($last_line_num) - strlen($current_line))
                    . $current_line
                    . ': </span>';
            }
            $out .= $line
                . '<br />'."\n";
            $line_counter++;
        }
        $out = '<code>'."\n".$out.'</code>';

        if ($return)
        {
            return $out;
        }
        else
        {
            echo $out;
        }
    }

    public static function formatAS3Sourcecode($str, $return = false, $counting = true, $first_line_num = '1', $font_color = '#666')
    {
        $str = '<?php '.$str;
        $str = self::formatPHPSourcecode($str,true);
        $str = str_replace('<span style="color: #0000BB">&lt;?php&nbsp;</span>','', $str);

        if ($return)
        {
            return $str;
        }
        else
        {
            echo $str;
        }
    }
} 