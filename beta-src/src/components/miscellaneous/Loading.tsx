import React, { FC, ReactElement, useState, useEffect } from "react";
import { motion } from "framer-motion";
import dagger from "../../assets/png/dagger-pencil-01.png";
import pencil from "../../assets/png/dagger-pencil-02.png";
import seasonIcons from "../../assets/png/season-icons.png";

interface WDUIProps {
  show: boolean;
  onLoadingFinished: () => void;
}

interface WDLoadingBarProps {
  percentage: number;
}

const WDLoadingBar: FC<WDLoadingBarProps> = function ({
  percentage,
}: WDLoadingBarProps): ReactElement {
  return (
    <div className="h-[7px] w-[200px] bg-[#565656] rounded-full mx-auto">
      <div
        className="h-[7px] rounded-full bg-gradient-to-r from-[#323E34] via-[#C8B897] to-white"
        style={{ width: `${percentage}%` }}
      />
    </div>
  );
};

const variants = {
  showLayover: {
    opacity: 1,
    y: 0,
    x: 0,
  },
  showPencil: {
    opacity: 1,
    y: 0,
    x: 0,
    transition: {
      delay: 3,
    },
  },
  showDagger: {
    opacity: 1,
    y: 0,
    x: 0,
    transition: {
      delay: 3,
    },
  },
  showIcons: {
    opacity: 1,
    transition: {
      delay: 0.5,
      duration: 2,
    },
  },
  showMessage: {
    opacity: 1,
    transition: {
      delay: 0.5,
      duration: 4,
    },
  },
  loadingUp: {
    opacity: 1,
    y: 0,
    transition: {
      delay: 3.5,
      duration: 0.5,
    },
  },
};

const WDLoading: FC<WDUIProps> = function ({
  show,
  onLoadingFinished,
}): ReactElement {
  const [percentage, setPercentage] = useState<number>(0);
  const [startLoading, setStartLoading] = useState<boolean>(false);
  const [startAnimations, setStartAnimations] = useState<boolean>(false);

  // eslint-disable-next-line consistent-return
  useEffect(() => {
    if (startLoading) {
      const interval = setInterval(() => {
        setPercentage((p) => p + 1);
      }, 100);
      if (percentage === 100) {
        clearInterval(interval);
        setStartLoading(false);
        onLoadingFinished();
      }
      return () => clearInterval(interval);
    }
  }, [startLoading, percentage]);

  return (
    <motion.div
      animate={show ? "showLayover" : "hideLayover"}
      variants={variants}
      style={{ y: -1500 }}
      onAnimationComplete={(definition) => {
        if (definition === "showLayover") {
          setStartAnimations(true);
        }
      }}
      className="absolute top-0 w-full h-full loading bg-loading bg-contain z-[9999] flex flex-col justify-center"
    >
      <div className="relative w-[70%] sm:w-[600px] mx-auto">
        <motion.img
          src={seasonIcons}
          alt="season icons"
          animate={startAnimations && "show2Icons"}
          variants={variants}
          className="mx-auto opacity-0"
        />
        <motion.img
          src={dagger}
          alt="diplomacy icon"
          animate="showDagger"
          style={{ x: "-100%", y: "-100%" }}
          variants={variants}
          className="absolute top-0 left-0 opacity-0"
        />
        <motion.img
          src={pencil}
          alt="diplomacy icon"
          animate="showPencil"
          style={{ x: "100%", y: "-100%" }}
          variants={variants}
          className="absolute top-0 left-0 opacity-0"
        />
      </div>
      <motion.div
        className="w-full text-white text-center uppercase text-lg tracking-[0.5rem] font-medium mt-20 sm:mt-26 opacity-0"
        animate="showMessage"
        variants={variants}
      >
        spring <br /> 1916
      </motion.div>
      <motion.div
        className="text-white mt-20"
        animate="loadingUp"
        variants={variants}
        style={{ y: 1000 }}
        onAnimationComplete={() => {
          setStartLoading(true);
        }}
      >
        <WDLoadingBar percentage={percentage} />
        <div className="uppercase pt-4 text-center tracking-[0.5rem]">
          Loading
        </div>
      </motion.div>
    </motion.div>
  );
};

export default WDLoading;
