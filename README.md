# Floor Plan API

---

## How to use

- Clone the repository with __git clone__
- Copy __.env.example__ file to __.env__ and edit database credentials there
- Run __composer install__
- Run __composer update__
- Run __php artisan key:generate__
- Run __php artisan storage:link__
- Run __php artisan migrate --seed__ (it has some seeded data for your testing)
- Run __For install the password then run some command __ (it has some seeded data for your testing)
- That's it: launch the main URL. 
- You can login to panel with default credentials __systemadmin@sobex.io__ - __123123123__
- Deploye on live server then debug mode off. Please open .env file and replace the code APP_DEBUG=false

## Setup the Cron Job

   To set up cron jobs for this project, follow these steps

   #### * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

  ### Example :

   #### * * * * * php8.3 /path-to-your-project/artisan schedule:run >> /dev/null 2>&1

   This runs Laravel's scheduler every minute, triggering tasks defined in Kernel.php.

   ### Manually Running Commands: You can manually run any scheduled command using artisan.

   #### To manually trigger the queue worker
    php artisan queue:work --stop-when-empty

   #### To manually send the first reminder
    php artisan notify:reminder first_reminder
   
   #### To manually send the final reminder
    php artisan notify:reminder final_reminder

   #### To manually assign the backup specialty
    php artisan notify:reminder assign_backup_speciality

   #### To manually check for backup specialty confirmations
    php artisan check:backup-speciality-confirmation



## License

Basically, feel free to use and re-use any way you want.

---

## More from our HIPL Team https://www.helpfulinsightsolution.com/
