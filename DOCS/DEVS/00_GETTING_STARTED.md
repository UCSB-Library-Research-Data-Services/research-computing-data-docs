# Getting Started

## In this document, you will review all the requirements and training for developing the Research Computing and Data Website.

## Firstly, you will have to review UCSB's Brand Guidelines to know what restrictions are in place for how to design a UCSB website. 

https://brand.ucsb.edu/our-story

https://ucsb.app.box.com/v/visual-identity-guidelines

https://webtheme.brand.ucsb.edu/docs/content-editors/menus-utility-bar-footer

https://webtheme.brand.ucsb.edu/support

https://webtheme.brand.ucsb.edu/pre-launch-checklist

## As a dev for the Research Computing and Data website, you will need to learn the basics of Drupal.  

### Pantheon has a crash-course for drupal 9 which I recommend going through: https://pantheon.io/drupal-9-training.  Note you have to scroll down to see the webinar recordings, and you do NOT have to sign up for the webinar. I understand that we are in Drupal 10 right now, but our website was initially built with drupal 9. In addition, Pantheon website has not released the training for drupal 10 yet. Drupal 9 and 10 are not too different.

### I also recommend getting more Drupal practice from Acquia which has free, in-depth Drupal courses: acquiaacademy.com/  Create an account, then start the "Drupal 9 Site Building Course" which is part of the "Drupal Developer Certification Learning Plan."  This should be a free course (as of May 2023). You will only need to complete the "Drupal 9 Site Building Course," and none of the other courses that are part of the learning plan, although Acquia is a good resource for any Drupal learning.  

- For Drupal 9 Site Building Course: You can skip Units 1-3 as they are not really relevant.  
- I recommend creating your own Drupal website on Pantheon in order to follow along the course.
- Pay extra attention and take notes on how to create and edit Content Types, creating custom form displays, building custom and complex views, and creating a taxonomy.  

## Go to the website
- First, go to https://dashboard.pantheon.io/login and login with your account.
  ![Screenshot (123)](https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01/assets/91364746/3ab0230b-304d-43b8-8e34-475f7e769c93)

- Then, click researchdata-ucsb-edu-v01 site. Your manager Renata will add you to the team so that you can join the group and view/edit this site.
- Next, click Site Admin
  ![Screenshot (122)](https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01/assets/91364746/bfa94e15-082b-46c4-ac2e-235c7017dc9d)

- Now log in with your account again
![Screenshot (124)](https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01/assets/91364746/53bfb4f0-0495-46ab-85fb-8ad808caa8db)

- You are now all set
![Screenshot (125)](https://github.com/UCSB-Library-Research-Data-Services/researchdata-ucsb-edu-v01/assets/91364746/2cc1bb70-b36a-4543-8073-9941ab017c7b)

## Adding New Users to Drupal

As a developer or site admin, you may need to add new team members to the Research Computing and Data website.

1. **Log in to the Site Admin**
   - Go to [Pantheon Dashboard](https://dashboard.pantheon.io/login) and log in with your account.  
   - Select the **researchdata-ucsb-edu-v01** site.  
   - Click **Site Admin** to enter the Drupal administrative interface.  

2. **Navigate to the People Section**
   - In the top menu bar, click on **People**.  
   - This is where all user accounts are managed.  

3. **Fill Out User Information**
   - **Email address**: Enter the user’s email (used for account recovery and notifications).  
   - **Check** "Allow user to log in via CAS" to allow them to log in through the UCSB portal.  
   - **Username**: Keep this the same username as their UCSB portal username.  
   - **Password / Confirm password**: Set a password (will not be actually used to log in).  
   - **Status**: Select **Active** to allow the account to log in.
   - 
4. **Save the New User**
   - Click **Create new account**.  
   - The new user will receive a confirmation email with login instructions.  

### Best Practices
- Always confirm with your manager (e.g., Renata) which role to assign before creating the account.  
- If someone only needs to update pages or add content, assign them the **Content Editor** role instead of **Administrator**.  
- Periodically review the **People** list to ensure only active team members have access.  

## Content and Code
After entering the website, every change would be either content changing or code changing.

### Content
All the contents are inside Drupal system which is host by Pantheon. You can add/edit/delete/... the contents inside drupal directly. You will comfortable using those inside drupal after your Drupal 101 class.

### Code
Even though UCSB drupal theme has a lot of templates for us to use, sometime we want to have more custom pages. In that case, we need to write some code. There are several ways to get the code and then edit and push the code. 

### For more details, check 01_CONTENT_&_CODE.md.
