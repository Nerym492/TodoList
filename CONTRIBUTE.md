# How to contribute ?

## Install the project

Follow the installation instructions in the [README](https://github.com/Nerym492/TodoList/blob/main/README.md).

## Use phpcs-fixer with Symfony rules

To follow the Symfony coding standards, you must use phpcs-fixer.
If you haven't already installed it, follow the instructions in the official documentation by clicking on the link below.  
https://cs.symfony.com/doc/installation.html


## Create issues on GitHub

Briefly describe the change you wish to make in the issue title. The issue name must begin with a verb using
present imperative.  
Example for a security fix : "Fix: csrf vulnerability in the login form"

## Create a branch for each issue

After creating the issue, you can create the corresponding branch in your local repository.  
For example, if the job title is : "Change: render of the task list page", 
then you could name the branch "change_task_list_render".  
```sh
git branch -b change_task_list_render
```

Make the necessary modifications to the branch, indicating what type of modification is made on each commit.   
The commit name must begin with a verb using present imperative.  
For an addition, the name will begin with "Add", for a modification "Change", and for an error correction "Fix", 
and so on...  
Example :
```sh
git commit -m "Change task creation button css"
```

## Test your branch

Remember to run the tests and add new tests if the changes you've made are not tested.  
```sh
php bin/phpunit --coverage-html tests/test-coverage
```
You can check whether your code is covered by opening the file tests/test-coverage/index.html in your web browser.  
Code coverage must remain above 70%.

## Push your branch and create a pull request

Once you have finished modifying the branch, you can push the branch to the remote directory.  
```sh
git push origin your_branch
```
Create a pull request that ask to merge your_branch into "main" branch.  
It will either be validated or rejected by the person managing the project.
