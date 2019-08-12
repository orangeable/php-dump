<?php

    class Dump {

        function Table($results) {
            $var_type = getType($results);
            $rows     = 0;
            $html     = '<table class="dump_table CLASS_NAME"><tbody><tr><th class="dump_caption CLASS_NAME" colspan="100%" onClick="javascript:dump_toggleTable(this);">INNER_TEXT</th></tr>';

            if ($var_type == "array" && $this->isStruct($results)) {
                $var_type = "struct";
                ksort($results);
            }

            switch ($var_type) {
                case "struct":
                    foreach($results as $key => $value) {
                        $html .= $this->Row($key, $value, $var_type);
                    }
                    $rows++;
                    break;

                case "array":
                    for ($i = 0; $i < sizeof($results); $i++) {
                        $html .= $this->Row($i, $results[$i], $var_type);
                    }
                    $rows++;
                    break;

                case "object":
                    if (get_class($results) == "mysqli_result") {
                        $var_type = "query";
                        $columns = [];
                        $num = 0;
                        $i = 0;

                        $html .= '<tr><th class="header ' . $var_type . '"></th>';

                        while ($row = mysqli_fetch_field($results)) {
                            array_push($columns, $row->name);

                            $html .= '<th class="header ' . $var_type . '">' . $columns[$i] . '</th>';
                            $i++;
                        }

                        $html .= '</tr>';

                        while ($row = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
                            $html .= '<tr><th class="header ' . $var_type . '">' . $num . '</th>';

                            for ($i = 0; $i < count($columns); $i++) {
                                $content = $row[$columns[$i]];
                                $content = str_replace("<", "&lt;", $content);
                                $content = str_replace(">", "&gt;", $content);
                                $html .= '<td>' . $content . '</td>';
                            }

                            $html .= '</tr>';
                            $num++;
                        }
                    }
                    else {
                        $object_vars = get_object_vars($results);

                        foreach($object_vars as $key => $value) {
                            $html .= $this->Row($key, $value, $var_type);
                        }

                        $class_methods = get_class_methods($results);

                        for ($i = 0; $i < sizeof($class_methods); $i++) {
                            $html .= $this->Row("method", $class_methods[$i], $var_type);
                        }
                    }

                    $rows++;
                    break;

                case "string":
                    $results = str_replace("<", "&lt;", $results);
                    $results = str_replace(">", "&gt;", $results);

                    $html .= $this->Row($var_type, $results);
                    $rows++;
                    break;

                case "boolean":
                    $boolean = ($results) ? "true" : "false";
                    $html .= $this->Row($var_type, $boolean);
                    break;

                default:
                    $html .= $this->Row($var_type, $results);
                    $rows++;
                    break;
            }

            if (!$rows) {
                $html .= '<tr><td colspan="2"><em>empty</td></tr>';
            }

            $html .= '</tbody></table>';
            $html = preg_replace("/CLASS_NAME/", $var_type, $html);
            $html = preg_replace("/INNER_TEXT/", $var_type, $html);

            return $html;
        }

        function Row($label, $content, $class = "") {
            $var_type = getType($content);
            $html     = '<tr><th class="header ' . $class . '" onClick=javascript:dump_toggleRow(this);">' . $label . '</th><td>';

            switch ($var_type) {
                case "struct":
                case "array":
                case "object":
                    $html .= $this->Table($content);
                    break;

                case "string":
                    $html .= $content;
                    break;

                case "boolean":
                    $html .= (($content) ? "true" : "false");
                    break;

                default:
                    if (is_null($content)) {
                        $html .= '<em>null</em>';
                    } else {
                        $html .= $content;
                    }
                    break;
            }

            $html .= '</td></tr>';

            return $html;
        }

        function Scripts() {
            $html = '
                <style type="text/css">
                    div.dump_wrap {
                        background-color: white;
                        font-family: Verdana, sans-serif;
                        color: black;
                    }

                    table.dump_table {
                        border-collapse: collapse;
                        background-color: white;
                        font-size: 7pt;
                    }

                    table.dump_table.struct tr th,
                    table.dump_table.struct tr td {
                        border: 2px solid #0000cc;
                    }

                    table.dump_table.array tr th,
                    table.dump_table.array tr td {
                        border: 2px solid #006600;
                    }

                    table.dump_table.query tr th,
                    table.dump_table.query tr td {
                        border: 2px solid #884488;
                    }

                    table.dump_table.object tr th,
                    table.dump_table.object tr td {
                        border: 2px solid #884444;
                    }

                    table.dump_table tr th.dump_caption {
                        color: white;
                        text-align: left;
                        padding: 5px;
                        cursor: pointer;
                    }

                    table.dump_table tr th.dump_caption.struct {
                        background-color: #4444cc;
                    }

                    table.dump_table tr th.dump_caption.array {
                        background-color: #378705;
                    }

                    table.dump_table tr th.dump_caption.query {
                        background-color: #aa66aa;
                    }

                    table.dump_table tr th.dump_caption.object {
                        background-color: #c75e5e;
                    }

                    table.dump_table tr th.header.struct {
                        background-color: #ccddff;
                    }

                    table.dump_table tr th.header.array {
                        background-color: #ccffcc;
                    }

                    table.dump_table tr th.header.query {
                        background-color: #ffddff;
                    }

                    table.dump_table tr th.header.object {
                        background-color: #e4d3d3;
                    }

                    table.dump_table tr th.header {
                        font-weight: normal;
                        text-align: left;
                        vertical-align: top;
                        cursor: pointer;
                    }

                    table.dump_table tr th,
                    table.dump_table tr td {
                        padding: 3px;
                    }
                </style>

                <script type="text/javascript">
                    function dump_toggleRow(source) {
                        var target = (document.all) ? source.parentElement.cells[1] : source.parentNode.lastChild;

                        dump_toggleTarget(target, dump_toggleSource(source));
                    }

                    function dump_toggleSource(source) {
                        if (source.style.fontStyle == "italic") {
                            source.style.fontStyle = "normal";
                            source.title = "collapse";
                            return "open";
                        }
                        else {
                            source.style.fontStyle = "italic";
                            source.title = "expand";
                            return "closed";
                        }
                    }

                    function dump_toggleTarget(target, switch_to_state) {
                        target.style.display = (switch_to_state == "open") ? "" : "none";
                    }

                    function dump_toggleTable(source) {
                        var switch_to_state = dump_toggleSource(source);

                        if (document.all) {
                            var table = source.parentElement.parentElement;

                            for (var i = 1; i < table.rows.length; i++) {
                                target = table.rows[1];
                                dump_toggleTarget(target, switch_to_state);
                            }
                        }
                        else {
                            var table = source.parentNode.parentNode;

                            for (var i = 1; i < table.childNodes.length; i++) {
                                target = table.childNodes[i];

                                if (target.style) {
                                    dump_toggleTarget(target, switch_to_state);
                                }
                            }
                        }
                    }
                </script>
            ';

            return $html;
        }

        function isStruct($array) {
            return array_keys($array) !== range(0, count($array) - 1);
        }

        function Output($var, $exit = false) {
            $dump = new Dump();

            echo $this->Scripts() .
                 '<div class="dump_wrap">' .
                 $this->Table($var) .
                 '</div>';

            if ($exit) {
                exit();
            }

            return true;
        }

    }

?>
