<?php 

class Post
{

    private $user_object;
    private $con;

    public function __construct($con, $user)
    {
        $this->con = $con;
        $this->user_object = new Users($con, $user);
    }

    public function submitPost($body_send, $user_to)
    {
        $body = strip_tags($body_send);// removes html tags
        $body = mysqli_real_escape_string($this->con, $body);// delete all space
        //keep the same format when user enter something
        $body = str_replace('\r\n', '\n', $body);
        $body = nl2br($body);

        $check_empty = preg_replace('/\s+/', '', $body);

        if ($check_empty != "") {
            // Current date and time
            $date_added = date("Y-m-d H:i:s");
            //Get username
            $added_by = $this->user_object->getUsername();
            // if user is on its own profile ,user_to is 'none'
            if ($user_to == $added_by) {
                $user_to = "none";
            }
            //insert post
            $query = mysqli_query($this->con, "INSERT INTO posts VALUES ('','$body','$added_by','$user_to','$date_added','no','no','0')");
            $returned_id = mysqli_insert_id($this->con);

            //Insert notification

            // Update post Count for user
            $num_posts = $this->user_object->getNumberOfPosts();
            $num_posts++;
            $update_query = mysqli_query($this->con, "UPDATE users SET num_posts ='$num_posts' WHERE username='$added_by' ");
        }
    }

    public function loadPostsFriends()
    {
      $str =""; // string to return
      $query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted='no' order by id desc ");
      while($row=mysqli_fetch_array($query)){
          $id=$row['id'];
          $body=$row['body'];
          $added_by =$row['added_by'];
          $date_time =$row['date_added'];

        if ($row['user_to']=='none'){
           $user_to='';
        }
        else{
           $user_to_obj=new Users($con,$row[$user_to]);
           $user_to_name =$user_to_obj->getFirstAndLastname();
            $user_to ="<a href='" . $row['user_to'] ."'>" .$user_to_name . "</a>" ;
        }
        //check if user who posted has their account closed
          $added_by_obj=new Users($con,$added_by);
         if ($added_by_obj->isClosed()){
             continue;
         }
          $user_details_query = mysqli_query($this->con, "SELECT first_name,last_name,profile_pic FROM users WHERE username='$added_by' ");
          $user_details_query_row=mysqli_fetch_array($user_details_query);

          //timeframe
          $date_time_now= date('y-m-d H:i:s');//format date
          $start_date = new DateTime($date_time_now);// time od the post
          $end_date = new DateTime($date_time_now);// current time  post
          $interval= $start_date->diff($end_date ); // difference betwen dates

          if ($interval->y >=1){
              if ($interval->y ==1)
              {
                  $time_message =$interval->y . "year ago";
              }
              else
                  $time_message =$interval->y . "years ago";
          }
           else if ($interval->d >=1){
                 if ($interval->d == 0){
                     $days= "ago";
                 }
             else  if ($interval->d ==1){
                   $days=$interval->d ." day ago";
               }
             else{
                 $days=$interval->d ." days ago";
             }
             if($interval->m==1){
                 $time_message =$interval->m . "month ago";

             }
             else{
                 $time_message =$interval->m . "months ago";

             }
           }
           else if($interval->d >=1){
               if ($interval->d ==1){
                   $days=" yesterday";
               }
               else{
                   $days=$interval->d ." days ago";

               }
           }


      }

    }
}
?>