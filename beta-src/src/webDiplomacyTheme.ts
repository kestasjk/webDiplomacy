import { createTheme } from "@mui/material/styles";

declare module "@mui/material/styles" {
  interface BreakpointOverrides {
    xs: false;
    sm: false;
    md: false;
    lg: false;
    xl: false;
    mobile: true;
    mobileLg: true;
    tablet: true;
    desktop: true;
  }

  interface TypographyVariants {
    label: React.CSSProperties;
  }

  export interface TypographyVariantsOptions {
    label?: React.CSSProperties;
  }
}

declare module "@mui/material/Typography" {
  interface TypographyPropsVariantOverrides {
    label: true;
  }
}

/**
 * constants
 * ===
 * define constants that are to be re-used in the theme. use generic names that
 * describe their usage.
 */
const activeButtonStyle = {
  backgroundColor: "#fff",
  boxShadow: "0 0 2px 2px #000",
  color: "#000",
};
const boldFontWeight = 700;
const disabledBackground = "#b8b8b8";
const disabledText = "#cacaca";
const disabledBackgroundSecondary = "transparent";
const disabledTextSecondary = "#bababa";
const defaultLineHeight = 1.2;
const normalFontWeight = 400;

/**
 * theme creation
 */
const webDiplomacyTheme = createTheme({
  breakpoints: {
    values: {
      mobile: 0,
      mobileLg: 414,
      tablet: 834,
      desktop: 1500,
    },
  },
  components: {
    MuiButton: {
      styleOverrides: {
        root: {
          borderRadius: 18,
          padding: "10px 18px",
        },
        containedPrimary: {
          "&:active": {
            ...activeButtonStyle,
          },
          "&:focus": {
            ...activeButtonStyle,
          },
          "&:hover": {
            backgroundColor: "#757575",
            color: "#fff",
          },
        },
        containedSecondary: {
          "&.Mui-disabled": {
            backgroundColor: disabledBackgroundSecondary,
            color: disabledTextSecondary,
          },
          "&:active": {
            ...activeButtonStyle,
          },
          "&:focus": {
            ...activeButtonStyle,
          },
          "&:hover": {
            backgroundColor: "#fafafa",
          },
        },
      },
    },
    MuiSvgIcon: {
      styleOverrides: {
        root: {
          "&.navIcon": {
            filter: "drop-shadow(0 0 7px #323232)",
            margin: 18,
          },
          "&.navIconSelected": {
            filter: "drop-shadow(1px 10px 7px #737373)",
            height: "52px",
            width: "53px",
          },
        },
      },
    },
    MuiIconButton: {
      styleOverrides: {
        root: {
          "&:hover": {
            background: "none",
          },
        },
      },
    },
  },
  palette: {
    action: {
      disabledBackground,
      disabled: disabledText,
    },
    error: {
      main: "#f00",
      contrastText: "#fff",
    },
    primary: {
      main: "#000",
      contrastText: "#fff",
    },
    secondary: {
      main: "#fff",
      contrastText: "#000",
    },
  },
  typography: {
    button: {
      fontSize: 12,
      fontWeight: boldFontWeight,
      lineHeight: defaultLineHeight,
      textTransform: "none",
    },
  },
});

/**
 * responsive overrides
 */
webDiplomacyTheme.typography.body1 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 14,
  fontWeight: normalFontWeight,
  lineHeight: defaultLineHeight,
};

webDiplomacyTheme.typography.h1 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 16,
  fontWeight: boldFontWeight,
  lineHeight: defaultLineHeight,
  [webDiplomacyTheme.breakpoints.up("desktop")]: {
    fontSize: 20,
  },
};

webDiplomacyTheme.typography.h2 = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 14,
  fontWeight: boldFontWeight,
  lineHeight: defaultLineHeight,
};

webDiplomacyTheme.typography.label = {
  fontFamily: webDiplomacyTheme.typography.fontFamily,
  fontSize: 10,
  fontWeight: normalFontWeight,
  lineHeight: 1,
};

export default webDiplomacyTheme;
