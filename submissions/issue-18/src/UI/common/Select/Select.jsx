import React, { useRef } from 'react';
import { MdKeyboardArrowDown, MdKeyboardArrowUp } from 'react-icons/md';
import cx from 'classnames';
import { useClickAway } from 'ahooks';
import styles from './Select.module.scss';

export const Option = ({ children, inactive, onClick, selected }) => {
  return (
    <div onClick={onClick} className={cx(styles.option, { [styles.inactive]: inactive, [styles.selected]: selected })}>
      {children}
    </div>
  );
};

export const Select = ({ children, label, value, onChange, className }) => {
  const [isOpen, setIsOpen] = React.useState(false);
  const ref = useRef();

  const [curVal, setCurVal] = React.useState(value);
  const curChild = React.Children.toArray(children).find(child => child.props.value === curVal);

  const newChildren = React.Children.map(children, child => {
    if (child.type !== Option) {
      throw new Error('Select only accepts Option children');
    }
    return React.cloneElement(child, {
      onClick: child?.props.inactive
        ? null
        : () => {
            setCurVal(child?.props.value);
            onChange(child?.props.value);
          },
      selected: child?.props.value === curVal,
    });
  });

  useClickAway(() => {
    setIsOpen(false);
  }, ref);

  const handleClick = () => {
    setIsOpen(!isOpen);
  };

  return (
    <div className={cx(styles.selectWrapper, className)}>
      <button
        style={{ borderRadius: isOpen ? '6px 0 0 0' : '6px 0 0 6px' }}
        className={cx(styles.select, className)}
        onClick={handleClick}
        ref={ref}
      >
        <span className={styles.label}>{label}</span>
        <span className={styles.value}>{curChild?.props?.value}</span>
        {isOpen ? (
          <MdKeyboardArrowUp className={styles.icon} size={32} />
        ) : (
          <MdKeyboardArrowDown className={styles.icon} size={32} />
        )}
      </button>
      {isOpen ? <div className={styles.options}>{newChildren}</div> : null}
    </div>
  );
};
