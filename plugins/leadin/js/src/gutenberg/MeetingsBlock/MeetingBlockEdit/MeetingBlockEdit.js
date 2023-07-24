import React, { Fragment } from 'react';
import PreviewMeeting from './PreviewMeeting';
import MeetingBlockController from './MeetingController';
import MeetingsContextWrapper from './MeetingsContext';

export default function MeetingBlockEdit({
  attributes: { url },
  isSelected,
  setAttributes,
}) {
  const handleChange = newUrl => {
    setAttributes({
      url: newUrl,
    });
  };

  return (
    <Fragment>
      {(isSelected || !url) && (
        <MeetingsContextWrapper url={url}>
          <MeetingBlockController handleChange={handleChange} />
        </MeetingsContextWrapper>
      )}
      {url && <PreviewMeeting url={url} />}
    </Fragment>
  );
}
