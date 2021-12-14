# Before doing anything...

## Download VS Code

Do **not** use VS Code @ FB. You need to download a separate instance of VS Code on your FB machine and use it for development on this project. This project requires developer tools and extensions that cannot be installed or configured on VS Code @ FB.

## Set up the project

Make sure you have followed the instructions for cloning this project and setting up the server and repository locally. There is detailed documentation for doing so. Please refer to your team for a link to this information. Once you have completed this portion, return to this documentation to set up for React development.

# Setting up your environment for React development in the `beta-src` folder.

Be sure you have run `npm install`.

## Summary

This project uses React with:

- Typescript
- ESLint with Airbnb rules
- Prettier

You must install the ESLint and Prettier VS Code extensions in VS Code. The extension identifiers are:

- `dbaeumer.vscode-eslint`
- `esbenp.prettier-vscode`

You do not need to do anything other than install these, as each have configuration files set up. However, it is recommended that you turn `Format On Save` to `checked` in your VS Code settings and make `Default Formatter` the `Prettier - Code Formatter` choice. This ensures that pull requests are formatted properly and there should be no formatting changes in your diff.

## Check your version of NodeJS

This React SPA uses the latest stable version of NodeJS (at the time of this writing 16.13.1).

To check you version of NodeJS:

```
node -v
```

It is recommened you use `n` to manage your versions of NodeJS:

```
sudo npm install -g n
```

then:

```
sudo n lts
```

More documentation on `n` is available [here](https://www.npmjs.com/package/n?activeTab=readme).

## npm install

Once you have made it to this point, make sure you have run `npm install`.

# Standard Create React App Boilerplate (still applicable)

This project was bootstrapped with [Create React App](https://github.com/facebook/create-react-app).

## Available Scripts

In the project directory, you can run:

### `npm start`

Runs the app in the development mode.\
Open [http://localhost:3000](http://localhost:3000) to view it in the browser.

The page will reload if you make edits.\
You will also see any lint errors in the console.

### `npm test`

Launches the test runner in the interactive watch mode.\
See the section about [running tests](https://facebook.github.io/create-react-app/docs/running-tests) for more information.

### `npm run build`

Builds the app for production to the `build` folder.\
It correctly bundles React in production mode and optimizes the build for the best performance.

The build is minified and the filenames include the hashes.\
Your app is ready to be deployed!

See the section about [deployment](https://facebook.github.io/create-react-app/docs/deployment) for more information.

### `npm run eject`

**Note: this is a one-way operation. Once you `eject`, you can’t go back!**

If you aren’t satisfied with the build tool and configuration choices, you can `eject` at any time. This command will remove the single build dependency from your project.

Instead, it will copy all the configuration files and the transitive dependencies (webpack, Babel, ESLint, etc) right into your project so you have full control over them. All of the commands except `eject` will still work, but they will point to the copied scripts so you can tweak them. At this point you’re on your own.

You don’t have to ever use `eject`. The curated feature set is suitable for small and middle deployments, and you shouldn’t feel obligated to use this feature. However we understand that this tool wouldn’t be useful if you couldn’t customize it when you are ready for it.

## Learn More

You can learn more in the [Create React App documentation](https://facebook.github.io/create-react-app/docs/getting-started).

To learn React, check out the [React documentation](https://reactjs.org/).
