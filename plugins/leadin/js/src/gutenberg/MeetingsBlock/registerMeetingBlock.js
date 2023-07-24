import React from 'react';
import { registerBlockType } from '@wordpress/blocks';
import CalendarIcon from '../Common/CalendarIcon';
import { connectionStatus, i18n } from '../../constants/leadinConfig';
import MeetingGutenbergPreview from './MeetingGutenbergPreview';
import MeetingSaveBlock from './MeetingSaveBlock';
import MeetingBlockEdit from './MeetingBlockEdit/MeetingBlockEdit';
import ErrorHandler from '../Common/ErrorHandler';

const ConnectionStatus = {
  Connected: 'Connected',
  NotConnected: 'NotConnected',
};

export default function registerMeetingBlock() {
  const editComponent = props => {
    if (props.attributes.preview) {
      return <MeetingGutenbergPreview />;
    } else if (connectionStatus === ConnectionStatus.Connected) {
      return <MeetingBlockEdit {...props} />;
    } else {
      return <ErrorHandler status={401} />;
    }
  };

  registerBlockType('leadin/hubspot-meeting-block', {
    title: i18n.meetingsBlockTitle,
    description: i18n.meetingsBlockDescription,
    icon: CalendarIcon,
    category: 'leadin-blocks',
    attributes: {
      url: {
        type: 'string',
        default: '',
      },
      preview: {
        type: 'boolean',
        default: false,
      },
    },
    example: {
      attributes: {
        preview: true,
      },
    },
    edit: editComponent,
    save: props => <MeetingSaveBlock {...props} />,
  });
}
