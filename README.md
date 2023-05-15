<p align="center">
  <img src="public/images/just-notams-logo-210.png" alt="Notams Logo">
  <br/>
  <br>WITH<br>
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</a>
</p>


# FixingNotams

## Overview

FixingNotams is a Laravel project aimed at improving the pilot's experience with NOTAMs (Notice to Airmen) during their briefings. This project was developed as a result of a [coding sprint](https://fixingnotams.org/notam-sprint/) that focused on finding solutions to enhance the sourcing, tagging, and filtering of NOTAMs based on airport types. The sprint has concluded, and this README provides a concise review of the project and instructions for getting started.

## Sprint Recap

During the sprint, the team tackled key challenges associated with NOTAM processing. The following solutions were developed:

1. **Sourcing of NOTAMs**: The project includes a mechanism to efficiently source NOTAMs from relevant authorities or data providers. This ensures the availability of up-to-date information for processing.

2. **Tagging System**: A comprehensive set of tags was created to categorize NOTAMs based on their nature. These tags cover various aspects, including ATC, Airport, Approach, Runway, Taxiway, Navigation, Airspace, Hazards, and Library. The tagging system allows for precise identification and organization of NOTAMs.

3. **Filtering Logic**: A matrix-based filtering mechanism was implemented to determine the visibility of NOTAMs based on the type of airport. This ensures that the displayed NOTAMs are relevant to the specific airport context, providing a more streamlined and focused briefing experience.

## Project Details

FixingNotams is a Laravel project that leverages the power of websockets to keep users continuously updated with the current status of pack generation. To ensure real-time updates, a webhook server must be set up and active for the project to function properly.

## Installation and Setup

To download and get started with the FixingNotams project, follow these steps:

1. Clone the repository to your local machine:

   ```
   git clone https://github.com/jonnywilliamson/notams.git
   ```

2. Install the project dependencies using Composer:

   ```
   composer install
   ```

3. Configure the environment variables by renaming the `.env.example` file to `.env` and updating the necessary values. 
   ```
   **Important** The notam source (ICAO) api key, the openai key and websockets section at the end of the file must be complete and correct for the project to work.
   ```

4. Generate an application key:

   ```
   php artisan key:generate
   ```

5. Set up the database by executing the migrations:

   ```
   php artisan migrate
   ```

6. Run the development server:

   ```
   php artisan serve
   ```

7. Set up the webhook server with your details to enable real-time updates. [Refer to the documentation](https://beyondco.de/docs/laravel-websockets/getting-started/introduction) for the specific steps and requirements related to webhook setup.

8. Access the FixingNotams application in your web browser at `http://localhost:8000` (or the specified URL).

## Contributing

Contributions to FixingNotams are welcome! If you encounter any issues or have ideas for improvements, please submit them via GitHub issues. You can also contribute by opening pull requests with your proposed changes.

## License

FixingNotams is software licensed under the [Creative Commons Attribution-NonCommercial 4.0 International License](https://creativecommons.org/licenses/by-nc/4.0/legalcode). Feel free to use, modify, and distribute this project as per the terms of the license.

## Support

If you need any assistance or have questions about FixingNotams, please reach out to the project maintainers or the community via the GitHub repository's issue tracker.

## Acknowledgements

We would like to express our gratitude to all the contributors and participants who made the FixingNotams project possible. Your dedication and efforts have played a significant role in advancing the field of NOTAM processing. Thank you for your valuable contributions!
