<?php
//901 is Math
//661 is English
//both A11


function get_scaled_score_function($attr) {

  $default_scale = array(1,1,2,3,3,4,5,5,6,6,7,7,8,8,9,9,9,10,10,10,11,11,11,12,12,13,13,14,14,14,15,15,15,15,16,16,16,17,
  17,17,18,19,19,20,20,20,21,21,21,22,22,22,23,23,23,24,24,25,25,25,26,27,27,28,29,30,31,32,33,34,34,35,35,36,36,36);

  $args = shortcode_atts( array(
    'scale' => $default_scale,
    'quiz_id' => 901,

  ), $attr);
  $scale = explode(",",$args['scale']);
  $currentuser = get_current_user_id();
  global $wpdb;
  $mysearch = "SELECT {$wpdb->prefix}learndash_user_activity.user_id, {$wpdb->prefix}learndash_user_activity_meta.activity_id, {$wpdb->prefix}learndash_user_activity_meta.activity_meta_key, {$wpdb->prefix}learndash_user_activity_meta.activity_meta_value
  FROM {$wpdb->prefix}learndash_user_activity
  JOIN {$wpdb->prefix}learndash_user_activity_meta ON {$wpdb->prefix}learndash_user_activity.activity_id={$wpdb->prefix}learndash_user_activity_meta.activity_id
  WHERE post_id = %d
  AND user_id=$currentuser
  AND activity_meta_key='score'";
  // $results = $wpdb->get_results($mysearch);
  $results = $wpdb->get_results(
    $wpdb->prepare($mysearch, $args['quiz_id'])
  );
  foreach( $results as $mydatas ) {
    if($mydatas->activity_meta_key=='score') {

       return $scale[$mydatas->activity_meta_value];
    }
  }
  return "No Results";
}

add_shortcode('get_scaled_score', 'get_scaled_score_function');

function find_scaled_score_function($scale_string, $quiz_id) {

  $scale = explode(",",$scale_string);
  $currentuser = get_current_user_id();
  global $wpdb;
  $mysearch = "SELECT {$wpdb->prefix}learndash_user_activity.user_id, {$wpdb->prefix}learndash_user_activity_meta.activity_id, {$wpdb->prefix}learndash_user_activity_meta.activity_meta_key, {$wpdb->prefix}learndash_user_activity_meta.activity_meta_value
  FROM {$wpdb->prefix}learndash_user_activity
  JOIN {$wpdb->prefix}learndash_user_activity_meta ON {$wpdb->prefix}learndash_user_activity.activity_id={$wpdb->prefix}learndash_user_activity_meta.activity_id
  WHERE post_id = %d
  AND user_id=$currentuser
  AND activity_meta_key='score'";
  // $results = $wpdb->get_results($mysearch);
  $results = $wpdb->get_results(
    $wpdb->prepare($mysearch, $quiz_id)
  );
  foreach( $results as $mydatas ) {
    if($mydatas->activity_meta_key=='score') {

       return $scale[$mydatas->activity_meta_value];
    }
  }
  return 0;
}

function get_test_score_function($attr) {

  $args = shortcode_atts( array(
    'english_quiz_id' => 901,
    'english_scale' => $default_scale,
    'math_quiz_id' => 901,
    'math_scale' => $default_scale,
    'reading_quiz_id' => 901,
    'reading_scale' => $default_scale,
    'science_quiz_id' => 901,
    'science_scale' => $default_scale

  ), $attr);
//scale,quiz
  $english = find_scaled_score_function($args['english_scale'], $args['english_quiz_id']);
  $math = find_scaled_score_function($args['math_scale'], $args['math_quiz_id']);
  $reading = find_scaled_score_function($args['reading_scale'], $args['reading_quiz_id']);
  $science = find_scaled_score_function($args['science_scale'], $args['science_quiz_id']);
  return round(($english+$math+$reading+$science)/4,0,PHP_ROUND_HALF_UP);

}

add_shortcode('get_test_score', 'get_test_score_function');

function get_problems_missed_function() {
  global $wpdb;
  $mysearch = "SELECT {$wpdb->prefix}learndash_pro_quiz_question.question, {$wpdb->prefix}learndash_pro_quiz_question.answer_data, {$wpdb->prefix}learndash_pro_quiz_statistic.answer_data as selections, {$wpdb->prefix}learndash_pro_quiz_statistic.points, {$wpdb->prefix}learndash_pro_quiz_question.category_id, {$wpdb->prefix}learndash_pro_quiz_category.category_name, {$wpdb->prefix}learndash_pro_quiz_question.correct_msg, {$wpdb->prefix}learndash_pro_quiz_question.incorrect_msg
    FROM {$wpdb->prefix}learndash_pro_quiz_statistic_ref
    JOIN {$wpdb->prefix}learndash_pro_quiz_statistic
    ON {$wpdb->prefix}learndash_pro_quiz_statistic_ref.statistic_ref_id = {$wpdb->prefix}learndash_pro_quiz_statistic.statistic_ref_id
    JOIN {$wpdb->prefix}learndash_pro_quiz_question
    ON {$wpdb->prefix}learndash_pro_quiz_question.id= {$wpdb->prefix}learndash_pro_quiz_statistic.question_id
    JOIN {$wpdb->prefix}learndash_pro_quiz_category
    ON {$wpdb->prefix}learndash_pro_quiz_category.category_id = {$wpdb->prefix}learndash_pro_quiz_question.category_id
    WHERE {$wpdb->prefix}learndash_pro_quiz_statistic_ref.user_id = 1
    AND {$wpdb->prefix}learndash_pro_quiz_statistic_ref.quiz_id = 2";
  // $mysearch = "SELECT * FROM {$wpdb->prefix}learndash_pro_quiz_statistic_ref";

    $results = $wpdb->get_results(
      $wpdb->prepare($mysearch)
    );

    // $results = $wpdb->get_results($mysearch);
    ?>
    <div class='container'>
<table class="table">
  <thead>
  <tr>
    <th style="width: 5%" scope="col"></th>
    <th style="width: 20%" scope="col">Correct Answer</th>
    <th style="width: 20%" scope="col">Your Answer</th>
    <th style="width: 20%" scope="col">Category</th>
  </tr>
</thead>
<tbody>
    <?php
    foreach ($results as $result){
        echo "<tr>";
        // $temp_str = $result->answer_data;
        // $temp_str = preg_replace('[^"][^"]', "", $result->answer_data);
        $temp_str = preg_replace('/[\"][^ABCDEFGHJK]/', "", $result->answer_data);
        $strben = preg_replace("/[^a-zA-Z0-9]/", "", $result->answer_data);
        //$strben = preg_replace("/[^ABCDEFGHJK]+/", "", $result->answer_data);
        // $strben = preg_replace("/a5i0O27WpProQuizModelAnswerTypes10s10answers2|s8htmlb0s10pointsi0s11correctb|s14sortStrings0s18sortStringHtmlb0s10gradedb0s22gradingProgressions15notgradednones14gradedTypeNs10mapperNi\dO27WpProQuizModelAnswerTypes10s10answers2|s14sortStrings0s18sortStringHtmlb0s10gradedb0s22gradingProgressions15notgradednones14gradedTypeNs10mapperN|s14sortStrings0s18sortStringHtmlb0s10gradeds0s22gradingProgressions15notgradednones14gradedTypes0s10mapperN|i\dO27WpProQuizModelAnswerTypes10s10answers2/", "", $strben);
        $length = strlen($strben);
        $answersEl = "";
        $selectionsEl = "";
        $selections = preg_replace("/\[|\]|,/", "", $result->selections);
        $selections = str_split($selections);
        $strben_arr = preg_replace("/[A-Z]/", "", $strben);
        $strben_arr = str_split($strben_arr);
        $str_key = array_search(1, $strben_arr);
        for ($x=0; $x < $length; $x+=1) {
          if ($x % 2 === 0 ) {
            $key = array_search(1, $selections);
            $correct = ($str_key == $key) ? "green" : "red";
            $text_decoration = ($str_key == $key) ? "none" : "line-through";
            $color = ($strben[$x+1]==1) ? 'green':'lightgray';
            $answersEl = $answersEl."<div style='color:".$color."'>".$strben[$x]."</div>";
            // $answersEl = $answersEl.'<div class="rb-tab">
            //     <div style="background-color:'.$color.'"  class="rb-spot">
            //       <span class="rb-txt">'.$strben[$x].'</span>
            //     </div>
            //   </div>';
            if (!strlen($selectionsEl)) {
              $selectionsEl = $selectionsEl.'<div>'.$strben[$key].'</div>';
            }
          }
        }

        // echo "<td>" . $strben[0].$strben[2].$strben[4].$strben[6].$strben[8]. "</td>";
        echo "<td style='color:".$correct.";text-decoration:".$text_decoration."'>" . $temp_str . "</td>";
        //echo "<td><div class='radio-group'><div id='rb-1' class='rb'>" . $answersEl . "</div></div></td>";
        echo "<td>" . $result->answer_data . "</td>";
        //echo "<td>" . $selectionsEl . "</td>";
        echo "<td style='color:".$correct.";text-decoration:".$text_decoration."'>" . $selectionsEl . "</td>";
        echo "<td>" . $result->category . "</td>";
        echo "</tr>";
        }
        ?>
        </tbody>
        </table>
      </div>
        <?php
}

add_shortcode('get_problems_missed', 'get_problems_missed_function');


 ?>
