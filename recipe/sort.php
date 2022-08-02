<?php

//新着順
function newadd($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star)
{
    $r['recipe'] = array_reverse($recipe);
    $r['recipe_ingredient'] = array_reverse($recipe_ingredient);
    $r['recipe_count'] = array_reverse($recipe_count);
    $r['recipe_value_average'] = array_reverse($recipe_value_average);
    $r['recipe_value_star'] = array_reverse($recipe_value_star);
    return $r;
}

//ヒットした個数順&使用食材少ない順
function hitcount_use_few($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star, $some = 0)
{
    for ($i = 0; $i < count($recipe_count) - 1; $i++) {
        $max = $i;
        for ($j = $i + 1; $j < count($recipe_count); $j++) {
            if ($recipe_count[$max] < $recipe_count[$j]) {
                $max = $j;
            }
        }
        $tmp = $recipe_count[$i];
        $recipe_count[$i] = $recipe_count[$max];
        $recipe_count[$max] = $tmp;

        $tmp = $recipe[$i];
        $recipe[$i] = $recipe[$max];
        $recipe[$max] = $tmp;

        $tmp = $recipe_ingredient[$i];
        $recipe_ingredient[$i] = $recipe_ingredient[$max];
        $recipe_ingredient[$max] = $tmp;

        $tmp = $recipe_value_average[$i];
        $recipe_value_average[$i] = $recipe_value_average[$max];
        $recipe_value_average[$max] = $tmp;

        $tmp = $recipe_value_star[$i];
        $recipe_value_star[$i] = $recipe_value_star[$max];
        $recipe_value_star[$max] = $tmp;
    }
    return few($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star, $some);
}

//ヒットした個数順&使用食材多い順
function hitcount_use_many($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star)
{
    return hitcount_use_few($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star, 1);
}

//人気順
function favorite($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star)
{
    for ($i = 0; $i < count($recipe_value_average) - 1; $i++) {
        $max = $i;
        for ($j = $i + 1; $j < count($recipe_value_average); $j++) {
            if ($recipe_value_average[$max] < $recipe_value_average[$j]) {
                $max = $j;
            }
        }
        $tmp = $recipe_count[$i];
        $recipe_count[$i] = $recipe_count[$max];
        $recipe_count[$max] = $tmp;

        $tmp = $recipe[$i];
        $recipe[$i] = $recipe[$max];
        $recipe[$max] = $tmp;

        $tmp = $recipe_ingredient[$i];
        $recipe_ingredient[$i] = $recipe_ingredient[$max];
        $recipe_ingredient[$max] = $tmp;

        $tmp = $recipe_value_average[$i];
        $recipe_value_average[$i] = $recipe_value_average[$max];
        $recipe_value_average[$max] = $tmp;

        $tmp = $recipe_value_star[$i];
        $recipe_value_star[$i] = $recipe_value_star[$max];
        $recipe_value_star[$max] = $tmp;
    }
    $r['recipe'] = $recipe;
    $r['recipe_ingredient'] = $recipe_ingredient;
    $r['recipe_count'] = $recipe_count;
    $r['recipe_value_average'] = $recipe_value_average;
    $r['recipe_value_star'] = $recipe_value_star;
    return $r;
}


//使用食材少ない順
function few($recipe, $recipe_ingredient, $recipe_count, $recipe_value_average, $recipe_value_star, $some)
{
    for ($i = 0; $i < count($recipe_count) - 1; $i++) {
        $min = $i;
        for ($j = $i + 1; $j < count($recipe_count); $j++) {
            if ($some === 1) {
                if (count($recipe_ingredient[$min]) < count($recipe_ingredient[$j]) && $recipe_count[$min] === $recipe_count[$j]) {
                    $min = $j;
                }
            } else if ($some === 0) {
                if (count($recipe_ingredient[$min]) > count($recipe_ingredient[$j]) && $recipe_count[$min] === $recipe_count[$j]) {
                    $min = $j;
                }
            }
        }
        $tmp = $recipe_count[$i];
        $recipe_count[$i] = $recipe_count[$min];
        $recipe_count[$min] = $tmp;

        $tmp = $recipe[$i];
        $recipe[$i] = $recipe[$min];
        $recipe[$min] = $tmp;

        $tmp = $recipe_ingredient[$i];
        $recipe_ingredient[$i] = $recipe_ingredient[$min];
        $recipe_ingredient[$min] = $tmp;

        $tmp = $recipe_value_average[$i];
        $recipe_value_average[$i] = $recipe_value_average[$min];
        $recipe_value_average[$min] = $tmp;

        $tmp = $recipe_value_star[$i];
        $recipe_value_star[$i] = $recipe_value_star[$min];
        $recipe_value_star[$min] = $tmp;
    }
    $r['recipe'] = $recipe;
    $r['recipe_ingredient'] = $recipe_ingredient;
    $r['recipe_count'] = $recipe_count;
    $r['recipe_value_average'] = $recipe_value_average;
    $r['recipe_value_star'] = $recipe_value_star;
    return $r;
}
