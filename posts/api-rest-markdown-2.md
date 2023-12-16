GitHub Branch: [Chapter #2 - Git](https://github.com/qbreis/api-rest-markdown/tree/dev-chapter-2-git)

# 2 Git Setup

In this chapter I want to create and set up new repo in GitHub, among other Git things.

>    Setup is one word when it is a noun (e.g., “it was a setup!”) or an adjective (e.g., “follow the setup instructions”). It is two words—set up—when it functions as a verb (e.g., “I'm going to set up the computer”).

## 2.1 Cretae new Git repo on GitHub

I go to [my GitHub account](https://github.com/qbreis) to create a new repository, I will name it just "api-rest-markdown" and I will choose "private" or "private".

Description: "Api Rest endpoint to get all posts data from one GitHub repository with all posts in Markdown format".

Adding a README file to adapt later.

I will use no .gitignore template and I select MIT license.

## 2.2 Setting up local Git Repo

Once in my project directory `api-rest-markdown` I run:

```bash
$ git init
```

Next thing I want to do is create new file .gitignore with just:

```
# Ignore vendor folder
/vendor/
```

This will prevent Git from tracking the `vendor` folder, to exclude this folder from version control and let developers manage their dependencies locally using the appropriate dependency management tools, in this case, basically `composer`.

I want to include `.gitignore` file in my Git repository and commit it along with other project files. This will allow for consistency across different contributors and development environments. By committing it to my repository, I make sure that everyone working on the project follows the same rules, which is crucial for collaboration and maintaining a clean, consistent codebase.

Now I will do my first `commit` with all files and folder (except `vendor` folder):

```bash
$ git add .
$ git commit -m 'chore: initial commit'
```

[Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) is a specification for writing standardized commit messages. It helps teams communicate the intent of changes in a structured way. 

Following, I want to rename the `master` branch in my local Git repository for the most popular term `main`, in order to move to a more inclusive, open culture and removing language like "master/slave" from this place:

```bash
$ git branch -m master main
```

>    If I would try to do this before any commit it would throw a typical error `error: refname refs/heads/master not found`!

## 2.3 Local Git configuration

To check state for Git repository I run `git status`.\
To check what is my GitHub user is set to I run: `$ git config user.name`.\
To check what is my GitHub email is set to I run: `$ git config user.email`.\
To change my GitHub user: `$ git config --local user.name "username"`.\
To change my GitHub user: `$ git config --local user.email "username@gmail.com"`.
To set up my GitHub credentials from my local: `git config --local credential.username "username"`.\

Once I have created my repo I run: `$ git remote add origin git@github.com:qbreis/api-rest-markdown.git`.

I will now do my first `push` forced to prevent refusing to merge unrelated stories:

```bash
$ git pull -f origin main
```

Doing so now I see I did lost my `LICENSE` and `README.md` files so I will update manually. I can check now in my repository [https://github.com/qbreis/api-rest-markdown](https://github.com/qbreis/api-rest-markdown).

Now I create dev branch:

```bash
$ git checkout -b dev
$ git push origin dev
```

I create still a new Git branch by running: `$ git checkout -b dev-chapter-1-md-to-html` and before pushing I want to adapt `README` file for this first chapter branch, I can check online [README.md](https://github.com/qbreis/api-rest-markdown/blob/dev-chapter-1-md-to-html/README.md) for this branch:

```bash
$ add .
$ commit -m 'feat: chapter #1 md to html'
$ git push origin dev-chapter-1-md-to-html-git-setup
```

## 2.4 Continuous Integration

I want to set up continuous integration through GitHub Actions to automate the deployment process whenever I push changes to GitHub repository.

For this purpose I will use one server online of my own. In my GitHub repo page I go *Settings*, under *Secrets and variables* I click *Actions* and *New repository secret* for:

Name: **ftp_host**
Secret: host
Replace host with my current host / IP web.

Name: **ftp_path**
Secret: /web/api-rest-markdown/

Name: **ftp_username**
Secret: username
Replace username with my current FTP username.

Name: **ftp_password**
Secret: password
Replace password with my current FTP password.

- I create new folder in my online server host called `/web/api-rest-markdown`.
- In my local server I create new Git branch for this chapter #2 by running `$ git checkout -b dev-chapter-2-git`.
- In my local server I create new `.github/workflows/main.yml` with following code:

```bash
on: push
name: 🚀 Deploy website on push
jobs:

  build:

    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Install Composer
      run: composer install

  web-deploy:
    name: 🎉 Deploy
    runs-on: ubuntu-latest
    steps:
    - name: 🚚 Get latest code
      uses: actions/checkout@v3

    - name: Cache Composer packages
      id: composer-cache
      uses: actions/cache@v3
      with:
        path: vendor
        key: ${{ runner.os }}-php-${{ hashFiles('**/composer.lock') }}
        restore-keys: |
          ${{ runner.os }}-php-

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress

    - name: Adding Vendor
      run: echo "vendor/:composer.lock" > .git-ftp-include
    
    - name: 📂 Sync files
      uses: SamKirkland/FTP-Deploy-Action@v4.3.4
      with:
        server: 134.0.15.63
        username: ${{ secrets.ftp_username }}
        password: ${{ secrets.ftp_password }}
        server-dir: ${{ secrets.ftp_path }}
```

Now I can check results online at [https://www.qbreis.com/api-rest-markdown/](https://www.qbreis.com/api-rest-markdown/).

I also want to do:

```bash
$ git checkout -b dev-chapter-2-git
$ git add .
$ git commit -m 'chore: add workflows main yml for deployment'
$ git push origin dev-chapter-2-git
```

## Reference links

- [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) - As a must.
- [Rename "master" branch to "main"](https://www.git-tower.com/learn/git/faq/git-rename-master-to-main) - Short article on renaming "master" in my own Git repositories to "main" (or any other term my team has chosen).
- [RenameGitBranch.md](https://gist.github.com/danieldogeanu/739f88ea5312aaa23180e162e3ae89ab) - Rename Git Branch By [danieldogeanu](https://gist.github.com/danieldogeanu).

## External links

- [Git - Wikipedia](https://en.wikipedia.org/wiki/Git) - Maybe I want to read about Git.
- [GitHub - Wikipedia](https://en.wikipedia.org/wiki/GitHub) - Maybe I want to know more about the hosting service I am using.