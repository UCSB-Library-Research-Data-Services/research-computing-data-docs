# Github and Pantheon Integration

As of right now, there is no automatic synchronization between the repositories in Github and Pantheon.  You must create two remote upstreams, pull from both, and then push to both when you're ready to push your code changes.

Here is how to do that according to pantheon's docs: https://pantheon.io/docs/guides/git/collaborative-development.

## Step 1: Git clone from Pantheon.

On the pantheon dashboard for the researchdata-ucsb-edu-v01 site, there is a Clone with Git dropdown. You need to use that to clone the repo into your local computer.  

## Step 2: Add this Github Repository as a Remote

You do this by: git remote add github [git clone URL]

Important note: Now you should now have two remote repositories connected to your local repo.  The Pantheon one is called 'origin,' and the github one is called 'github'.

## Step 3: Pushing any code changes: 

When you push a code change, you must push to both branches, and also make sure there are no merge conflicts.  To do this, you must git pull from both 'origin' and 'github' remote repositories.

When finished, you can git push to both remote repositories.  

## Branching Workflows:

Github and Pantheon both have their own ways of creating branches, then merging them into the main code.  Github uses Pull Requests to do this.  In Pantheon, it is with 'multidev.'

## Synchronizing Github and Pantheon:

This must be done manually for now.  If any updates were done to Github, you must git pull them to your local repo, then you can push them to the Pantheon repo.
  
